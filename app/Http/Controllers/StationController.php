<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Station;

class StationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des stations accessibles
     */
    public function showStationSelector()
    {
        $user = Auth::user();
        $stations = $user->stations()->withCount(['shifts', 'tanks'])->get();
        
        // Si une seule station, la sélectionner automatiquement
        if ($stations->count() === 1) {
            $user->setActiveStation($stations->first()->id);
            return redirect()->route('manager.index_form');
        }
        
        return view('manager.station-selector', compact('stations'));
    }

    /**
     * Sélectionner une station
     */
    public function selectStation(Request $request)
    {
        $request->validate([
            'station_id' => 'required|exists:stations,id'
        ]);
        
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a accès à cette station
        if (!$user->hasAccessToStation($request->station_id)) {
            return redirect()->back()->with('error', 'Accès non autorisé à cette station.');
        }
        
        $user->setActiveStation($request->station_id);
        
        return redirect()->route('manager.index_form')->with('success', 'Station sélectionnée avec succès.');
    }

    /**
     * Dashboard multi-stations
     */
    public function dashboard()
    {
        $user = Auth::user();
        $stations = $user->stations()->with(['tanks', 'shifts' => function($query) {
            $query->orderBy('date_shift', 'desc')->limit(5);
        }])->get();
        
        $activeStation = $user->getActiveStation();
        
        // Statistiques globales
        $globalStats = [
            'totalStations' => $stations->count(),
            'totalShifts' => 0,
            'totalSales' => 0,
            'totalStockValue' => 0,
            'criticalTanks' => 0
        ];
        
        // Calculer les pourcentages par station
        foreach ($stations as $station) {
            $station->fill_percentage_gazole = $station->getFillPercentage('gazole');
            $station->fill_percentage_super = $station->getFillPercentage('super');
            $station->days_of_supply_gazole = $station->getDaysOfSupply('gazole');
            $station->days_of_supply_super = $station->getDaysOfSupply('super');
            
            // Stock en valeur
            $stockValue = 0;
            $gazolePrice = $station->getCurrentPrice('gazole');
            $superPrice = $station->getCurrentPrice('super');
            
            if ($gazolePrice) {
                $stockValue += $station->getTotalStock('gazole') * $gazolePrice->price_per_liter;
            }
            
            if ($superPrice) {
                $stockValue += $station->getTotalStock('super') * $superPrice->price_per_liter;
            }
            
            $station->stock_value = $stockValue;
            $globalStats['totalStockValue'] += $stockValue;
            
            // Compter les cuves critiques
            foreach ($station->tanks as $tank) {
                if ($tank->is_low) {
                    $globalStats['criticalTanks']++;
                }
            }
        }
        
        return view('manager.multi-station-dashboard', compact('stations', 'activeStation', 'globalStats'));
    }

    /**
     * Détail d'une station spécifique
     */
    public function showStation($id)
    {
        $user = Auth::user();
        $station = $user->stations()->with(['tanks', 'fuelPrices'])->findOrFail($id);
        
        // Vérifier les permissions
        if (!$user->hasAccessToStation($id)) {
            abort(403, 'Accès non autorisé');
        }
        
        // Calculer les statistiques détaillées
        $stats = [
            'totalStockGazole' => $station->getTotalStock('gazole'),
            'totalStockSuper' => $station->getTotalStock('super'),
            'fillPercentageGazole' => $station->getFillPercentage('gazole'),
            'fillPercentageSuper' => $station->getFillPercentage('super'),
            'capacityGazole' => $station->getTotalCapacity('gazole'),
            'capacitySuper' => $station->getTotalCapacity('super'),
            'daysOfSupplyGazole' => $station->getDaysOfSupply('gazole'),
            'daysOfSupplySuper' => $station->getDaysOfSupply('super'),
            'monthlySales' => $station->getTotalSales(now()->subDays(30), now()),
            'monthlyVolume' => $station->getTotalVolume(now()->subDays(30), now())
        ];
        
        // Derniers shifts
        $recentShifts = $station->shifts()
            ->with('pompeDetails')
            ->orderBy('date_shift', 'desc')
            ->limit(10)
            ->get();
        
        // Dernières livraisons
        $recentDeliveries = $station->fuelDeliveries()
            ->with('tank')
            ->orderBy('delivery_date', 'desc')
            ->limit(10)
            ->get();
        
        // Alertes de stock bas
        $lowStockAlerts = $station->tanks()
            ->where('is_active', true)
            ->whereColumn('current_level', '<=', 'minimum_threshold')
            ->get();
        
        return view('manager.station-detail', compact('station', 'stats', 'recentShifts', 'recentDeliveries', 'lowStockAlerts'));
    }
}
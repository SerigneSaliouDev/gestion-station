<?php

namespace App\Http\Controllers;

use App\Models\FuelType;
use App\Models\FuelPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PriceController;

class PriceController extends Controller
{
    /**
     * Types de carburant disponibles
     */
    private $fuelTypes = [
        'super' => ['id' => 'super', 'name' => 'Super'],
        'gazole' => ['id' => 'gazole', 'name' => 'Gazole'],
        
    ];

    /**
     * Afficher le formulaire de modification des prix
     */
    public function editPrices()
    {
        // Préparer les types de carburant avec leurs prix actuels
        $fuelTypes = collect($this->fuelTypes)->map(function ($fuel, $key) {
            return [
                'id' => $fuel['id'],
                'name' => $fuel['name'],
                'current_price' => $this->getCurrentPrice($fuel['id']),
            ];
        })->values(); // Convertir en collection et prendre les valeurs

        // Historique des modifications (les 10 dernières)
        $priceHistory = FuelPrice::with('changer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('manager.prices.edit', [
            'fuelTypes' => $fuelTypes,
            'priceHistory' => $priceHistory
        ]);
    }

    /**
     * Mettre à jour les prix
     */
   public function updatePrices(Request $request)
{
    $request->validate([
        'prices' => 'required|array',
        'prices.*.fuel_type' => 'required|string|in:super,gazole,premium',
        'prices.*.price_per_liter' => 'required|numeric|min:0|max:10000',
        'change_reason' => 'required|string|min:10|max:500',
    ]);

    DB::beginTransaction();

    try {
        $changedPrices = [];

        foreach ($request->prices as $priceData) {
            $oldPrice = $this->getCurrentPrice($priceData['fuel_type']);
            
            // Vérifier si le prix a changé
            if ($oldPrice != $priceData['price_per_liter']) {
                // ENREGISTRER LE NOUVEAU PRIX
                $fuelPrice = \App\Models\FuelPrice::create([
                    'fuel_type' => $priceData['fuel_type'],
                    'price_per_liter' => $priceData['price_per_liter'],
                    'changed_by' => Auth::id(),
                    'change_reason' => $request->change_reason,
                ]);

                $changedPrices[] = [
                    'fuel_type' => $priceData['fuel_type'],
                    'old_price' => $oldPrice,
                    'new_price' => $priceData['price_per_liter'],
                    'change_date' => now(),
                ];
            }
        }

        DB::commit();

        return redirect()->route('manager.edit_prices')
            ->with('success', count($changedPrices) . ' prix mis à jour avec succès!')
            ->with('changed_prices', $changedPrices);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Erreur lors de la mise à jour des prix: ' . $e->getMessage());
    }
}

    /**
     * Obtenir le prix actuel d'un carburant
     */
    private function getCurrentPrice($fuelType)
    {
        $price = FuelPrice::where('fuel_type', $fuelType)
            ->latest()
            ->first();

        return $price ? $price->price_per_liter : 0;
    }

    /**
     * Historique complet des prix
     */
    public function priceHistory(Request $request)
    {
        $query = FuelPrice::with('changer');

        if ($request->has('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $history = $query->orderBy('created_at', 'desc')->paginate(20);

        $fuelTypes = FuelPrice::select('fuel_type')->distinct()->pluck('fuel_type');

        return view('manager.prices.history', compact('history', 'fuelTypes'));
    }
}
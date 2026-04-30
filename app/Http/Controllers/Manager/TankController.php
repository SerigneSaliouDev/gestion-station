<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Tank;
use App\Models\Station;
use App\Traits\FuelTypeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TankController extends Controller
{
    
    
    public function create()
    {
        return view('manager.tanks.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|string|unique:tanks,number,NULL,id,station_id,' . Auth::user()->station_id,
            'fuel_type' => 'required|in:super,gasoil,ESSENCE PIROGUE,essence pirogue,essence_pirogue',
            'capacity' => 'required|integer|min:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            $stationId = Auth::user()->station_id;
            // Normaliser le type de carburant avant de l'enregistrer
            $fuelType = $this->normalizeFuelType($request->fuel_type);
            $capacity = $request->capacity;
            
            // Créer la cuve avec le type normalisé
            $tank = Tank::create([
                'number' => $request->number,
                'fuel_type' => $fuelType, // Utiliser le type normalisé
                'capacity' => $capacity,
                'description' => $request->description ?? 'Cuve ' . $request->number,
                'station_id' => $stationId,
            ]);
            
            // Mettre à jour les capacités de la station
            $this->updateStationCapacities($stationId);
            
            DB::commit();
            
            return redirect('/manager/tanks/liste')
                ->with('success', 'Cuve créée avec succès ! Les capacités de la station ont été mises à jour.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de la création: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function index()
    {
        $tanks = Tank::where('station_id', Auth::user()->station_id)
            ->orderBy('fuel_type')
            ->orderBy('number')
            ->get();
            
        return view('manager.tanks.index', compact('tanks'));
    }
    
    public function edit($id)
    {
        $tank = Tank::where('station_id', Auth::user()->station_id)
            ->findOrFail($id);
            
        return view('manager.tanks.edit', compact('tank'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'number' => 'required|string|unique:tanks,number,' . $id . ',id,station_id,' . Auth::user()->station_id,
            'fuel_type' => 'required|in:super,gasoil,ESSENCE PIROGUE,essence pirogue,essence_pirogue',
            'capacity' => 'required|integer|min:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            $stationId = Auth::user()->station_id;
            $tank = Tank::where('station_id', $stationId)->findOrFail($id);
            
            // Normaliser le type de carburant
            $newFuelType = $this->normalizeFuelType($request->fuel_type);
            
            // Mettre à jour la cuve
            $tank->update([
                'number' => $request->number,
                'fuel_type' => $newFuelType,
                'capacity' => $request->capacity,
                'description' => $request->description ?? 'Cuve ' . $request->number,
            ]);
            
            // Mettre à jour les capacités de la station
            $this->updateStationCapacities($stationId);
            
            DB::commit();
            
            return redirect('/manager/tanks/liste')
                ->with('success', 'Cuve mise à jour avec succès ! Les capacités de la station ont été ajustées.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Normaliser le type de carburant
     */
    private function normalizeFuelType($fuelType)
    {
        $fuelType = strtolower(trim($fuelType));
        
        $normalization = [
            'super' => 'super',
            'gasoil' => 'gasoil',
            'gazole' => 'gasoil',
            'diesel' => 'gasoil',
            'essence_pirogue' => 'essence_pirogue',
            'essence-pirogue' => 'essence_pirogue',
            'pirogue' => 'essence_pirogue',
            'essence pirogue' => 'essence_pirogue',
            'essence pirogue' => 'essence_pirogue',
        ];
        
        return $normalization[$fuelType] ?? $fuelType;
    }
    
    /**
     * Obtenir la colonne de capacité correspondante
     */
    private function getStationCapacityColumn($fuelType)
    {
        $fuelType = $this->normalizeFuelType($fuelType);
        
        $mapping = [
            'super' => 'capacite_super',
            'gasoil' => 'capacite_gazole',
            'essence_pirogue' => 'capacite_essence_pirogue',
        ];
        
        return $mapping[$fuelType] ?? null;
    }
    
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $stationId = Auth::user()->station_id;
            $tank = Tank::where('station_id', $stationId)->findOrFail($id);
            
            // Supprimer la cuve
            $tank->delete();
            
            // Mettre à jour les capacités de la station
            $this->updateStationCapacities($stationId);
            
            DB::commit();
            
            return redirect('/manager/tanks/liste')
                ->with('success', 'Cuve supprimée avec succès ! Les capacités de la station ont été mises à jour.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
    
    /**
     * Mettre à jour les capacités de la station en fonction des cuves
     */
    private function updateStationCapacities($stationId)
    {
        $capacities = Tank::where('station_id', $stationId)
            ->selectRaw('fuel_type, SUM(capacity) as total_capacity')
            ->groupBy('fuel_type')
            ->get()
            ->keyBy('fuel_type');
        
        $updateData = [
            'capacite_super' => 0,
            'capacite_gazole' => 0,
            'capacite_essence_pirogue' => 0, // Ajouter cette ligne
        ];
        
        foreach ($capacities as $fuelType => $data) {
            $column = $this->getStationCapacityColumn($fuelType);
            if ($column && isset($updateData[$column])) {
                $updateData[$column] = $data->total_capacity;
            }
        }
        
        Station::where('id', $stationId)->update($updateData);
    }
}
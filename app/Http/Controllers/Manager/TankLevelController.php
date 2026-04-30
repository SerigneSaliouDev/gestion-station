<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Tank;
use App\Models\TankLevel;
use App\Services\TankCalibrationService;
use App\Traits\TankLevelTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TankLevelController extends Controller
{
    use TankLevelTrait;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:manager');
        $this->middleware('checkStation');
    }

    /**
     * Afficher le formulaire de jaugeage
     */
    public function create()
    {
        try {
            $stationId = Auth::user()->station_id;
            
            $tanks = Tank::where('station_id', $stationId)
                ->orderBy('fuel_type')
                ->orderBy('number')
                ->get();
            
            if ($tanks->isEmpty()) {
                return redirect()->route('manager.tanks.create')
                    ->with('warning', 'Aucune cuve configurée. Créez d\'abord des cuves.');
            }
            
            $tanksData = [];
            foreach ($tanks as $tank) {
                $fillPercentage = $tank->capacity > 0 
                    ? ($tank->current_volume / $tank->capacity) * 100 
                    : 0;
                
                // Vérifier si une table de calibration existe
                $hasCalibration = TankCalibrationService::hasCalibrationTable($tank->capacity);
                $calibrationDetails = $hasCalibration 
                    ? TankCalibrationService::getTableDetails($tank->capacity)
                    : null;
                
                $tanksData[] = [
                    'id' => $tank->id,
                    'number' => $tank->number,
                    'fuel_type' => $tank->fuel_type,
                    'type_display' => TankCalibrationService::getDisplayName($tank->fuel_type),
                    'description' => $tank->description ?? 'Cuve ' . $tank->number,
                    'capacity' => $tank->capacity,
                    'current_volume' => $tank->current_volume ?? 0,
                    'available_capacity' => $tank->capacity - ($tank->current_volume ?? 0),
                    'fill_percentage' => round($fillPercentage, 1),
                    'progress_class' => TankCalibrationService::getProgressBarClass($fillPercentage),
                    'badge_class' => TankCalibrationService::getFuelTypeBadgeClass($tank->fuel_type),
                    'icon' => TankCalibrationService::getFuelTypeIcon($tank->fuel_type),
                    'color' => TankCalibrationService::getFuelTypeColor($tank->fuel_type),
                    'tolerance' => TankCalibrationService::getToleranceThreshold($tank->fuel_type),
                    'has_calibration_table' => $hasCalibration,
                    'calibration_range' => $calibrationDetails ? [
                        'min_cm' => $calibrationDetails['min_height_cm'],
                        'max_cm' => $calibrationDetails['max_height_cm']
                    ] : null,
                ];
            }
            
            return view('manager.tank-levels.create', [
                'tanks' => $tanksData,
                'max_height' => 400,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur création formulaire jaugeage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('manager.stocks.dashboard')
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer un jaugeage
     */
    public function store(Request $request)
    {
        $request->validate([
            'tank_id' => 'required|exists:tanks,id',
            'level_cm' => 'required|numeric|min:0|max:400',
            'measurement_date' => 'required|date',
            'temperature_c' => 'nullable|numeric|min:-10|max:60',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            $tank = Tank::findOrFail($request->tank_id);
            
            // Vérification de la station
            if ($tank->station_id !== $user->station_id) {
                return back()->with('error', 'Cuve non autorisée pour votre station.');
            }
            
            // Validation de la mesure
            $validation = $this->validateGauging($request->level_cm);
            if (!$validation['valid']) {
                return back()->with('error', implode(', ', $validation['errors']))
                    ->withInput();
            }
            
            // Validation avec la table de calibration
            $calibrationValidation = TankCalibrationService::validateMeasurement(
                $tank->capacity,
                $request->level_cm,
                $tank->fuel_type
            );
            
            if (!$calibrationValidation['valid']) {
                return back()->with('error', $calibrationValidation['message'])
                    ->withInput();
            }
            
            // Calcul du volume avec le trait
            $calculation = $this->calculateVolumeFromHeight(
                $tank,
                $request->level_cm,
                $request->temperature_c
            );
            
            // Préparation des données
            $tankLevelData = [
                'tank_number' => $tank->number,
                'station_id' => $user->station_id,
                'measurement_date' => $request->measurement_date,
                'level_cm' => $request->level_cm,
                'temperature_c' => $request->temperature_c ?? 20,
                'fuel_type' => $tank->fuel_type,
                'product_category' => $this->getProductCategory($tank->fuel_type),
                
                // Stocks (vos colonnes existent déjà)
                'theoretical_stock' => $calculation['theoretical_stock'],
                'physical_stock' => $calculation['corrected_volume'],
                'volume_liters' => $calculation['corrected_volume'],
                
                // Différences
                'difference' => $calculation['difference'],
                'difference_percentage' => $calculation['difference_per_mille'], // en ‰
                
                // Correction température
                'temperature_corrected' => $request->filled('temperature_c') ? 1 : 0,
                'correction_factor' => $request->filled('temperature_c') 
                    ? $this->calculateCorrectionFactor($request->temperature_c, $tank->fuel_type)
                    : null,
                'uncorrected_volume' => $calculation['raw_volume'],
                
                // Tolérance et acceptabilité
                'tolerance_threshold' => $calculation['tolerance'], // en ‰
                'is_acceptable' => $calculation['is_acceptable'] ? 1 : 0,
                
                // Méthode de calcul
                'calculation_method' => TankCalibrationService::hasCalibrationTable($tank->capacity) 
                    ? 'table_calibration' 
                    : 'generic_formula',
                
                // Notes et observations
                'observations' => $request->notes,
                'correction_notes' => $this->buildCorrectionNotes($request, $calculation),
                'measured_by' => $user->id,
            ];
            
            // Création de l'enregistrement
            $tankLevel = TankLevel::create($tankLevelData);
            
            // Mise à jour de la cuve
            $tank->update([
                'current_volume' => $calculation['corrected_volume'],
                'last_measured_at' => $request->measurement_date,
                'current_level_cm' => $request->level_cm,
                'last_measurement_date' => now(),
            ]);
            
            DB::commit();
            
            // Message de confirmation
            $message = $this->buildGaugingSuccessMessage($tank, $calculation, $request->all());
            
            // Si écart hors tolérance, ajouter un warning
            if (!$calculation['is_acceptable']) {
                return redirect()->route('manager.tank-levels.create')
                    ->with('success', $message)
                    ->with('warning', $this->buildToleranceErrorMessage($tank, $calculation));
            }
            
            return redirect()->route('manager.tank-levels.create')
                ->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur enregistrement jaugeage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'tank_id' => $request->tank_id,
                'level_cm' => $request->level_cm,
                'temperature_c' => $request->temperature_c
            ]);
            
            return back()->with('error', 'Erreur lors du jaugeage: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * API pour calculer le volume en temps réel
     */
    public function calculateVolumeApi(Request $request)
    {
        $request->validate([
            'tank_id' => 'required|exists:tanks,id',
            'height_cm' => 'required|numeric|min:0|max:400',
            'temperature_c' => 'nullable|numeric',
        ]);
        
        try {
            $user = Auth::user();
            $tank = Tank::findOrFail($request->tank_id);
            
            if ($tank->station_id !== $user->station_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuve non autorisée.'
                ], 403);
            }
            
            // Validation avec la table de calibration
            $calibrationValidation = TankCalibrationService::validateMeasurement(
                $tank->capacity,
                $request->height_cm,
                $tank->fuel_type
            );
            
            if (!$calibrationValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $calibrationValidation['message'],
                    'range' => $calibrationValidation['range'] ?? null
                ], 400);
            }
            
            // Calcul du volume
            $calculation = $this->calculateVolumeFromHeight(
                $tank,
                $request->height_cm,
                $request->temperature_c
            );
            
            // Classes CSS pour le statut
            $statusClass = $this->getDifferenceStatusClass(
                $calculation['difference_per_mille'],
                $calculation['tolerance']
            );
            
            return response()->json([
                'success' => true,
                'volume' => $calculation['corrected_volume'],
                'volume_formatted' => number_format($calculation['corrected_volume'], 0, ',', ' ') . ' L',
                'uncorrected_volume' => $calculation['raw_volume'],
                'difference' => $calculation['difference'],
                'difference_formatted' => $this->formatDifference(
                    $calculation['difference'],
                    $calculation['difference_per_mille']
                ),
                'difference_per_mille' => $calculation['difference_per_mille'],
                'difference_per_mille_formatted' => ($calculation['difference_per_mille'] >= 0 ? '+' : '') . 
                                                    number_format(abs($calculation['difference_per_mille']), 1) . '‰',
                'is_acceptable' => $calculation['is_acceptable'],
                'status_class' => $statusClass,
                'fill_percentage' => $calculation['fill_percentage'],
                'tank' => [
                    'number' => $tank->number,
                    'fuel_type' => $tank->fuel_type,
                    'type_display' => TankCalibrationService::getDisplayName($tank->fuel_type),
                    'capacity' => $tank->capacity,
                    'current_volume' => $tank->current_volume,
                    'available_capacity' => $tank->capacity - ($tank->current_volume ?? 0),
                    'tolerance' => $calculation['tolerance'],
                    'tolerance_display' => $calculation['tolerance'] . '‰',
                ],
                'calculation' => [
                    'height_cm' => $request->height_cm,
                    'height_mm' => $request->height_cm * 10,
                    'temperature_c' => $request->temperature_c ?? 20,
                    'method' => TankCalibrationService::hasCalibrationTable($tank->capacity) 
                        ? 'table_calibration' 
                        : 'generic_formula',
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur API calcul volume', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tank_id' => $request->tank_id,
                'height_cm' => $request->height_cm
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur de calcul: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Historique des jaugeages
     */
    public function history(Request $request)
    {
        $stationId = Auth::user()->station_id;
        
        $query = TankLevel::where('station_id', $stationId)
            ->with('tank')
            ->orderBy('measurement_date', 'desc')
            ->orderBy('created_at', 'desc');
        
        // Filtre par cuve
        if ($request->filled('tank_id')) {
            $query->where('tank_id', $request->tank_id);
        }
        
        // Filtre par date
        if ($request->filled('start_date')) {
            $query->whereDate('measurement_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('measurement_date', '<=', $request->end_date);
        }
        
        // Filtre par statut d'acceptabilité
        if ($request->filled('status')) {
            $isAcceptable = $request->status === 'acceptable';
            $query->where('is_acceptable', $isAcceptable);
        }
        
        $gaugings = $query->paginate(20);
        
        // Liste des cuves pour le filtre
        $tanks = Tank::where('station_id', $stationId)
            ->orderBy('number')
            ->get(['id', 'number', 'fuel_type']);
        
        return view('manager.tank-levels.history', compact('gaugings', 'tanks'));
    }

    /**
     * Afficher les détails d'un jaugeage
     */
    public function show($id)
    {
        $stationId = Auth::user()->station_id;
        
        $gauging = TankLevel::where('station_id', $stationId)
            ->with('tank')
            ->findOrFail($id);
        
        return view('manager.tank-levels.show', compact('gauging'));
    }

    /**
     * Dashboard des jaugeages avec comparaison
     */
    public function dashboard()
    {
        $stationId = Auth::user()->station_id;
        
        $tanks = Tank::where('station_id', $stationId)
            ->with(['latestGauging' => function($query) {
                $query->latest('measurement_date')->limit(1);
            }])
            ->orderBy('fuel_type')
            ->orderBy('number')
            ->get()
            ->map(function($tank) {
                $lastGauging = $tank->latestGauging;
                $physicalStock = $lastGauging ? $lastGauging->physical_stock : $tank->current_volume;
                $theoreticalStock = $tank->current_volume;
                $difference = $physicalStock - $theoreticalStock;
                $differencePerMille = $theoreticalStock > 0 
                    ? ($difference / $theoreticalStock) * 1000 
                    : 0;
                $tolerance = TankCalibrationService::getToleranceThreshold($tank->fuel_type);
                
                return [
                    'tank' => $tank,
                    'theoretical_stock' => $theoreticalStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $difference,
                    'difference_formatted' => $this->formatDifference($difference, $differencePerMille),
                    'difference_per_mille' => round($differencePerMille, 1),
                    'tolerance' => $tolerance,
                    'is_acceptable' => abs($differencePerMille) <= $tolerance,
                    'last_gauging_date' => $lastGauging ? $lastGauging->measurement_date : null,
                    'status_class' => $this->getDifferenceStatusClass($differencePerMille, $tolerance),
                    'fill_percentage' => $tank->capacity > 0 
                        ? ($physicalStock / $tank->capacity) * 100 
                        : 0,
                ];
            });
        
        // Statistiques globales
        $stats = [
            'total_tanks' => $tanks->count(),
            'acceptable_gaugings' => $tanks->where('is_acceptable', true)->count(),
            'critical_gaugings' => $tanks->where('is_acceptable', false)->count(),
            'total_positive_deviation' => $tanks->sum(function($t) {
                return $t['difference'] > 0 ? $t['difference'] : 0;
            }),
            'total_negative_deviation' => $tanks->sum(function($t) {
                return $t['difference'] < 0 ? abs($t['difference']) : 0;
            }),
        ];
        
        return view('manager.tank-levels.dashboard', compact('tanks', 'stats'));
    }
}
<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ChiefController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AdminController; 
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\PdfReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Manager\TankController;
use App\Http\Controllers\Manager\TankLevelController;
// ==================== ROUTES PUBLIQUES ====================

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');
    Route::get('/debug/middleware', function() {
        dd([
            'auth' => auth()->check(),
            'user' => auth()->user(),
            'role' => auth()->user() ? auth()->user()->getRoleNames() : null,
            'station_id' => auth()->user() ? auth()->user()->station_id : null,
            'session' => session()->all()
        ]);
    })->middleware(['auth', 'role:manager', 'checkStation']);

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        
        if ($user->hasRole('administrateur')) {
            return redirect()->route('admin.users.index');
        } 
        elseif ($user->hasRole('manager')) {
            return redirect()->route('manager.stocks.dashboard');
        } 
        elseif ($user->hasRole('charge-operations') || $user->hasRole('chief')) {
            return redirect()->route('chief.dashboard');
        }
        
        return redirect()->route('manager.index_form'); 
    }
    return redirect('/login');
});

// ==================== ROUTES PROTÉGÉES ====================

Route::middleware(['auth'])->group(function () {
    
    
    // ==================== ROUTES PDF (EN PREMIER) ====================
    Route::prefix('pdf')->name('pdf.')->group(function () {
        Route::get('/station-report/{stationId?}', [PdfReportController::class, 'stationReport'])
            ->name('station.report');
        
        Route::get('/reconciliation-report', [PdfReportController::class, 'reconciliationReport'])
            ->name('reconciliation.report');
        
        Route::get('/inventory-report', [PdfReportController::class, 'inventoryReport'])
            ->name('inventory.report');
        
        Route::get('/sales-by-pump', [PdfReportController::class, 'salesByPumpReport'])
            ->name('sales-by-pump.report');
        
        Route::get('/shift-report/{shiftId}', [PdfReportController::class, 'shiftReport'])
            ->name('shift.report');
    });
    
    // Route GET pour afficher le sélecteur de station
    Route::get('/select-station', [StationController::class, 'showStationSelector'])
        ->name('station.select');
    
    // Route POST pour traiter la sélection de station
    Route::post('/select-station', [StationController::class, 'selectStation'])
        ->name('station.select.post');
    

   // ==================== ROUTES MANAGER ====================
Route::middleware(['auth', 'role:manager', 'checkStation'])->prefix('manager')->name('manager.')->group(function () {
    
    // ==================== ROUTES PRINCIPALES SIMPLES ====================
    
    // 1. TABLEAU DE BORD (UNE SEULE ROUTE)
    Route::get('/stocks/dashboard', [StockController::class, 'dashboard'])->name('stocks.dashboard');
    
    // 2. ROUTES POUR LES CUVES (UTILISANT TankController)
    Route::prefix('tanks')->name('tanks.')->group(function () {
        Route::get('/creer', [TankController::class, 'create'])->name('create');
        Route::post('/enregistrer', [TankController::class, 'store'])->name('store');
        Route::get('/liste', [TankController::class, 'index'])->name('index');
    });
    
    // 3. RÉCEPTIONS (version simplifiée)
    Route::prefix('stocks/receptions')->name('stocks.receptions.')->group(function () {
        Route::get('/creer', function() {
            $stationId = auth()->user()->station_id;
            $tanks = \App\Models\Tank::where('station_id', $stationId)->get();
            
            if ($tanks->isEmpty()) {
                return redirect()->route('manager.tanks.create')
                    ->with('error', 'Créez d\'abord des cuves !');
            }
            
            return view('manager.stocks.receptions.create', [
                'tanks' => $tanks
            ]);
        })->name('create');
        
        Route::post('/', [StockController::class, 'store'])->name('store');
    });
    
    // 4. JAUGEAGES
    Route::prefix('tank-levels')->name('tank-levels.')->group(function () {
        Route::get('/create', [TankLevelController::class, 'create'])->name('create');
        Route::post('/', [TankLevelController::class, 'store'])->name('store');
        Route::post('/calculate-volume', [TankLevelController::class, 'calculateVolumeApi'])->name('calculate-volume');
    });

    
    // 5. VÉRIFICATION DE STOCK 
    Route::post('/check-stock-before-save', [ManagerController::class, 'checkStockBeforeSave'])->name('check-stock-before-save');
    Route::post('/check-stock-for-type', [ManagerController::class, 'checkStockForType'])->name('check-stock-for-type'); // CORRECTION: Déplacée ici
    
    // ==================== AUTRES ROUTES EXISTANTES ====================
    Route::prefix('stocks')->name('stocks.')->group(function () {
        // Route dashboard déjà définie plus haut, ne pas la redéfinir
        
        // Historique et bilan
        Route::get('/history', [StockController::class, 'movementHistory'])->name('history');
        Route::get('/balance', [StockController::class, 'stockBalance'])->name('balance');
        
        // Ajustements
        Route::get('/adjustments/create', [StockController::class, 'createAdjustment'])->name('adjustments.create');
        Route::post('/adjustments', [StockController::class, 'storeAdjustment'])->name('adjustments.store');
        
        // Rapports
        Route::get('/reports/reconciliation', [StockController::class, 'reconciliationReport'])->name('reports.reconciliation');
        Route::get('/reports/inventory', [StockController::class, 'inventoryReport'])->name('reports.inventory');
        
        // API
        Route::get('/api/tanks-by-fuel-type', [StockController::class, 'getTanksByFuelType'])
            ->name('api.tanks-by-fuel-type');
        Route::get('/api/current-stocks', [StockController::class, 'apiCurrentStocks'])->name('api.current-stocks');
        Route::get('/api/stock-history/{fuelType?}', [StockController::class, 'apiStockHistory'])->name('api.stock-history');
       
    });
    
    Route::get('/calibration', [StockController::class, 'calibrationManagement'])
        ->name('calibration.management');
    Route::post('/calibration/import', [StockController::class, 'importCalibration'])
        ->name('calibration.import');
    
    // ==================== ROUTES VENTES ====================
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/create', [SaleController::class, 'create'])->name('create');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{id}', [SaleController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [SaleController::class, 'cancel'])->name('cancel');
        Route::get('/report', [SaleController::class, 'report'])->name('report');
        
        // API pour vérifier le stock (spécifique aux ventes)
        Route::post('/check-tank-stock', [SaleController::class, 'checkTankStock'])->name('check-tank-stock');
        Route::get('/get-tanks-by-fuel-type', [SaleController::class, 'getTanksByFuelType'])->name('get-tanks-by-fuel-type');
    });
    
    // ==================== ROUTES GÉNÉRALES MANAGER ====================
    Route::get('/saisie-index', [ManagerController::class, 'showIndexForm'])->name('index_form');
    Route::post('/manager/store-index', [ManagerController::class, 'storeIndex'])
            ->middleware(['auth', 'stock.guard:shift'])
            ->name('store_index');      
    
    Route::get('/history', [ManagerController::class, 'history'])->name('history');
    Route::get('/history/{id}', [ManagerController::class, 'show'])->name('history.show');
    
    Route::prefix('saisie')->name('saisie.')->group(function () {
        Route::get('/{id}', [ManagerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ManagerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ManagerController::class, 'update'])->name('update');
        Route::delete('/{id}', [ManagerController::class, 'destroy'])->name('delete');
        Route::get('/{id}/pdf', [ManagerController::class, 'generatePdf'])->name('pdf');
        Route::get('/{id}/print', [ManagerController::class, 'print'])->name('print');
        Route::get('/depense/{id}/download', [ManagerController::class, 'downloadJustificatif'])->name('download.justificatif');
    });
    
    Route::get('/rapports', [ManagerController::class, 'reports'])->name('reports');
    Route::post('/rapports/pdf', [ManagerController::class, 'generateReportPdf'])->name('reports.pdf');
    Route::get('/saisie/{id}/pdf-shift', [ManagerController::class, 'generateShiftPdf'])->name('saisie.pdf.shift');
    Route::post('/export-reports', [ManagerController::class, 'exportReports'])->name('export.reports');
    
    Route::get('/prices/edit', [PriceController::class, 'editPrices'])->name('edit_prices');
    Route::post('/prices/update', [PriceController::class, 'updatePrices'])->name('update_prices');
    Route::get('/prices/history', [PriceController::class, 'priceHistory'])->name('price_history');
});
    
    // ==================== ROUTES CHIEF ====================
    Route::middleware(['auth', 'role:chief|charge-operations'])
        ->prefix('chief')
        ->name('chief.')
        ->group(function () {
    
        Route::get('/dashboard', [ChiefController::class, 'dashboard'])->name('dashboard');
        
        
        Route::get('/stations', [ChiefController::class, 'stations'])->name('stations');
        Route::get('/stations/create', [ChiefController::class, 'createStation'])->name('stations.create');
        Route::post('/stations', [ChiefController::class, 'storeStation'])->name('stations.store');
        Route::get('/stations/{id}', [ChiefController::class, 'showStation'])->name('stations.show');
        Route::get('/stations/{id}/edit', [ChiefController::class, 'editStation'])->name('stations.edit');
        Route::put('/stations/{id}', [ChiefController::class, 'updateStation'])->name('stations.update');
        Route::delete('/stations/{id}', [ChiefController::class, 'destroyStation'])->name('stations.destroy');
        
        Route::get('/rapports/pompistes', [ChiefController::class, 'analysePompistes'])
            ->name('rapports.pompistes');
        Route::get('/chief/stations/{station}/refresh-stock', [ChiefController::class, 'refreshStockData'])
            ->name('chief.stations.refresh-stock');
        
        Route::prefix('stations')->name('stations.')->group(function () {
            Route::get('/capacites', [ChiefController::class, 'capacites'])->name('capacites.index');
            Route::get('/{station}/capacites/edit', [ChiefController::class, 'editCapacites'])->name('capacites.edit');
            Route::put('/{station}/capacites', [ChiefController::class, 'updateCapacites'])->name('capacites.update');
            Route::get('/chief/debug/dashboard-sales', [ChiefController::class, 'debugSalesDashboard'])
                    ->middleware(['auth', 'role:chief'])
                    ->name('chief.debug.dashboard-sales');
        });
        
        Route::get('/sales-evolution', [ChiefController::class, 'salesEvolution'])
            ->name('sales-evolution');
        
        Route::get('/validations', [ChiefController::class, 'validations'])->name('validations');
        Route::get('/validation/{id}', [ChiefController::class, 'showValidation'])->name('validation.show');
        Route::post('/validation/{id}/valider', [ChiefController::class, 'validerSaisie'])->name('validation.valider');
        Route::post('/validation/{id}/rejeter', [ChiefController::class, 'rejeterSaisie'])->name('validation.rejeter');
        
        Route::get('/pending-count', function() {
            $count = \App\Models\ShiftSaisie::where('statut', 'en_attente')->count();
            return response()->json(['count' => $count]);
        })->name('pending-count');
        
        Route::get('/rapports/stations', [ChiefController::class, 'rapportsStations'])->name('rapports.stations');
        Route::get('/rapports/pdf', [ChiefController::class, 'genererRapportPDF'])->name('rapports.pdf');
        Route::get('/rapports/station/{id}', [ChiefController::class, 'rapportStationSpecifique'])->name('rapports.station');
        
        Route::get('/utilisateurs', [ChiefController::class, 'utilisateurs'])->name('utilisateurs');
    });
    
    // ==================== ROUTES ADMINISTRATEUR ====================
    // CORRECTION : Utilisez 'role:administrateur' au lieu de 'role:admin'
  Route::middleware(['auth', 'role:administrateur'])->prefix('admin')->name('admin.')->group(function () {
    
    
     // Route racine (/admin) redirige vers la liste des utilisateurs
    Route::get('/', function () {
        return redirect()->route('admin.users.index');
    });
    
    
    // ==================== GESTION DES UTILISATEURS ====================
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/create', [AdminController::class, 'create'])->name('create');
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::get('/{user}', [AdminController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [AdminController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminController::class, 'destroy'])->name('destroy');
        Route::put('/{user}/password/reset', [AdminController::class, 'resetPassword'])->name('password.reset');
    });
    
    // ==================== RAPPORTS (CORRIGÉ) ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        // Dashboard des rapports
        Route::get('/dashboard', function () {
            return view('admin.reports.dashboard');
        })->name('dashboard');
        
        // Autres rapports
        Route::get('/daily', [AdminController::class, 'dailyReport'])->name('daily');
        Route::get('/monthly', [AdminController::class, 'monthlyReport'])->name('monthly');
        Route::get('/station-comparison', [AdminController::class, 'stationComparison'])->name('station.comparison');
    });
    
    // ==================== AUTRES ROUTES EXISTANTES ====================
    
    // Statistiques AJAX pour le dashboard
    Route::get('/dashboard/stats', [AdminController::class, 'dashboardStats'])->name('dashboard.stats');
    
    // Maintenance et Supervision
    Route::get('/supervision/maintenance', [AdminController::class, 'maintenance'])->name('supervision.maintenance.index');
    Route::post('/maintenance', [AdminController::class, 'storeMaintenance'])->name('maintenance.store');
    
    // Tarification
    Route::get('/supervision/pricing', [AdminController::class, 'pricing'])->name('supervision.pricing');
    Route::put('/pricing/update', [AdminController::class, 'updatePricing'])->name('fuel-prices.update');
    
    // Corrections de données
    Route::get('/supervision/corrections', [AdminController::class, 'dataCorrections'])->name('supervision.data.corrections');
    Route::post('/corrections/shift/{shift}', [AdminController::class, 'correctShift'])->name('corrections.shift');
    
    // Validations
    Route::get('/validations/pending', [AdminController::class, 'pendingValidations'])->name('validations.pending');
    Route::get('/validations/station/{stationId}', [AdminController::class, 'stationValidations'])->name('validations.station');
    Route::get('/validations/{id}', [AdminController::class, 'showValidation'])->name('validations.show');
    Route::post('/validations/{id}/validate', [AdminController::class, 'validateShift'])->name('validations.validate');
    Route::post('/validations/{id}/reject', [AdminController::class, 'rejectShift'])->name('validations.reject');
    
    // Corrections
    Route::get('/corrections', [AdminController::class, 'correctionsIndex'])->name('corrections.index');
    Route::get('/corrections/{id}', [AdminController::class, 'showCorrection'])->name('corrections.show');
    Route::post('/corrections/{id}/resolve', [AdminController::class, 'resolveCorrection'])->name('corrections.resolve');
    
    // Stations
  // Groupe pour les routes stations
    Route::prefix('stations')->name('stations.')->group(function () {
        Route::get('/', [AdminController::class, 'stationsIndex'])->name('index');
        Route::get('/create', [AdminController::class, 'createStation'])->name('create');
        Route::post('/', [AdminController::class, 'storeStation'])->name('store');
        Route::get('/{id}', [AdminController::class, 'showStation'])->name('show');
        Route::get('/{id}/edit', [AdminController::class, 'editStation'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'updateStation'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'destroyStation'])->name('destroy');
    
    // Route pour mettre à jour le manager
    Route::post('/{id}/update-manager', [AdminController::class, 'updateStationManager'])
        ->name('update-manager');
});
        Route::prefix('shifts')->name('shifts.')->group(function () {
        // Liste de tous les shifts
        Route::get('/', [AdminController::class, 'shiftsIndex'])->name('index');
        
        // Shifts d'aujourd'hui (existe déjà)
        Route::get('/today', [AdminController::class, 'todayShifts'])->name('today');
        
        // Détails d'un shift
        Route::get('/{shift}', [AdminController::class, 'showShift'])->name('show');
        
        // Éditer un shift
        Route::get('/{shift}/edit', [AdminController::class, 'editShift'])->name('edit');
        
        // Mettre à jour un shift
        Route::put('/{shift}', [AdminController::class, 'updateShift'])->name('update');
        
        // Supprimer un shift
        Route::delete('/{shift}', [AdminController::class, 'destroyShift'])->name('destroy');
    });
    
    // Shifts (aujourd'hui)
    Route::get('/shifts/today', [AdminController::class, 'todayShifts'])->name('shifts.today');
    
   
    
    // Route de débogage (si elle existe)
    Route::get('/debug-info', function() {
        return response()->json([
            'user' => auth()->user(),
            'session' => session()->all(),
        ]);
    })->name('debug.info');
});

    Route::get('/test-email', function() {
    try {
        $user = App\Models\User::first();
        $password = 'test123';
        
        Mail::to('destination@email.com')->send(new App\Mail\UserWelcomeMail($user, $password));
        
        return "Email envoyé avec succès ! Vérifiez la boîte de réception.";
    } catch (\Exception $e) {
        return "Erreur : " . $e->getMessage();
    }
});
    
    // ==================== ROUTES POUR LA GESTION DES PRIX ====================
    Route::prefix('fuel-prices')->name('fuel-prices.')->group(function () {
        Route::get('/edit', [FuelController::class, 'edit'])->name('edit');
        Route::put('/update', [FuelController::class, 'update'])->name('update');
        Route::get('/history', [FuelController::class, 'history'])->name('history');
        Route::get('/debug-stocks', [StockController::class, 'debugStocks'])
            ->middleware(['auth', 'role:manager|operations_chief|admin'])
            ->name('debug.stocks');
    });
    
    Route::get('/debug/stocks', [StockController::class, 'debugGasOilData'])
        ->middleware(['auth', 'role:manager|operations_chief|admin'])
        ->name('debug.stocks');
        
    Route::get('/debug-chief-stock', [ChiefController::class, 'debugStockData'])
        ->middleware(['auth', 'role:chief|charge-operations'])
        ->name('debug.chief.stock');
});



Route::prefix('supervision')->name('supervision.')->group(function () {
    // Tarification
    Route::get('/pricing', function () {
        $prices = \App\Models\FuelPrice::latest()->first();
        return view('admin.supervision.pricing', compact('prices'));
    })->name('pricing');
    
    // Correction de données
    Route::get('/data-corrections', function () {
        $corrections = []; // Données de démonstration
        $stations = \App\Models\Station::all();
        return view('admin.supervision.data-corrections', compact('corrections', 'stations'));
    })->name('data.corrections');
    
    // Maintenance
    Route::get('/maintenance', function () {
        $stations = \App\Models\Station::all();
        return view('admin.supervision.maintenance.index', compact('stations'));
    })->name('maintenance.index');
});

    // Reporting
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.reports.dashboard');
        })->name('dashboard');
            
    Route::get('/station-comparison', function () {
            $stations = \App\Models\Station::all();
            $startDate = request('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = request('end_date', now()->format('Y-m-d'));
            
            return view('admin.reports.station-comparison', compact('stations', 'startDate', 'endDate'));
        })->name('station.comparison');
    });

// ==================== ROUTES FALLBACK ====================
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
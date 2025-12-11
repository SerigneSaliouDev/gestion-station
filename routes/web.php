<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\ChiefController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SaleController; // Import ajouté

// Routes d'authentification
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

// Route racine - Point de redirection unique après connexion
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        
        // **********************************************
        // * LOGIQUE DE REDIRECTION APRÈS CONNEXION *
        // **********************************************

        if ($user->hasRole('administrateur')) {
            return redirect()->route('admin.dashboard');
        } 
        
        elseif ($user->hasRole('manager')) {
            return redirect()->route('manager.index_form');
        } 
        
        // Utilisation des deux rôles pour la validation
        elseif ($user->hasRole('charge-operations') || $user->hasRole('chief')) {
            return redirect()->route('chief.validations');
        }
        
        // Fallback par défaut 
        return redirect()->route('manager.index_form'); 
    }
    return redirect('/login');
});

// Groupe de routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    
    // ******************************************************
    // * 🔑 ROUTES DE SÉLECTION DE STATION (Accessibles d'abord) *
    // ******************************************************
    
    // Route GET pour afficher le sélecteur de station
    Route::get('/select-station', [StationController::class, 'showStationSelector'])
        ->name('station.select');
    
    // Route POST pour traiter la sélection de station
    Route::post('/select-station', [StationController::class, 'selectStation'])
        ->name('station.select.post');
    
    // ******************************************************
    
    // Routes Manager
    Route::middleware(['role:manager', 'checkStation'])->prefix('manager')->name('manager.')->group(function () {
        
        // ==================== ROUTES STOCKS ====================
        Route::prefix('stocks')->name('stocks.')->group(function () {
            
            // Tableau de bord
            Route::get('/dashboard', [StockController::class, 'dashboard'])->name('dashboard');
            
            // Réception de Carburant
            Route::get('/receptions/create', [StockController::class, 'createReception'])->name('receptions.create');
            // Correction de la route post pour réception
            Route::post('/receptions', [StockController::class, 'storeReception'])->name('receptions.store');
            
            // Jaugeages de Cuve
            Route::get('/tank-levels/create', [StockController::class, 'createTankLevel'])->name('tank-levels.create');
            Route::post('/tank-levels', [StockController::class, 'storeTankLevel'])->name('tank-levels.store');
            
            // Rapports Stock
            Route::get('/reports/reconciliation', [StockController::class, 'reconciliationReport'])->name('reports.reconciliation');
            Route::get('/reports/inventory', [StockController::class, 'inventoryReport'])->name('reports.inventory');
            
            // APIs
            Route::get('/api/current-stocks', [StockController::class, 'apiCurrentStocks'])->name('api.current-stocks');
            Route::get('/api/stock-history/{fuelType?}', [StockController::class, 'apiStockHistory'])->name('api.stock-history');
        });
        
        // ==================== ROUTES VENTES ====================
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/', [SaleController::class, 'index'])->name('index');
            Route::get('/create', [SaleController::class, 'create'])->name('create');
            Route::post('/', [SaleController::class, 'store'])->name('store');
            Route::get('/{id}', [SaleController::class, 'show'])->name('show');
            Route::post('/{id}/cancel', [SaleController::class, 'cancel'])->name('cancel');
            Route::get('/report', [SaleController::class, 'report'])->name('report');
        });
        
        // ==================== ROUTES GÉNÉRALES MANAGER ====================
        // Formulaire de saisie (page principale pour manager)
        Route::get('/saisie-index', [ManagerController::class, 'showIndexForm'])->name('index_form');
        Route::post('/saisie-index', [ManagerController::class, 'storeIndex'])->name('store_index');
        
        // Historique
        Route::get('/history', [ManagerController::class, 'history'])->name('history');
        Route::get('/history/{id}', [ManagerController::class, 'show'])->name('history.show');
        
        // Gestion des saisies
        Route::prefix('saisie')->name('saisie.')->group(function () {
            Route::get('/{id}', [ManagerController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [ManagerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ManagerController::class, 'update'])->name('update');
            Route::delete('/{id}', [ManagerController::class, 'destroy'])->name('delete');
            Route::get('/{id}/pdf', [ManagerController::class, 'generatePdf'])->name('pdf');
            Route::get('/{id}/print', [ManagerController::class, 'print'])->name('print');
            Route::get('/depense/{id}/download', [ManagerController::class, 'downloadJustificatif'])->name('download.justificatif');
        });
        
        // Rapports
        Route::get('/rapports', [ManagerController::class, 'reports'])->name('reports');
        Route::post('/rapports/pdf', [ManagerController::class, 'generateReportPdf'])->name('reports.pdf');
        Route::get('/saisie/{id}/pdf-shift', [ManagerController::class, 'generateShiftPdf'])->name('saisie.pdf.shift');
            Route::get('/dashboard', [StockController::class, 'dashboard'])->name('dashboard');
        
        // Jaugeage des cuves
        Route::get('/tank-levels/create', [StockController::class, 'createTankLevel'])->name('tank-levels.create');
        Route::post('/tank-levels', [StockController::class, 'storeTankLevel'])->name('tank-levels.store');
            
        // Gestion des prix carburants
        Route::get('/prices/edit', [PriceController::class, 'editPrices'])->name('edit_prices');
        Route::post('/prices/update', [PriceController::class, 'updatePrices'])->name('update_prices');
        Route::get('/prices/history', [PriceController::class, 'priceHistory'])->name('price_history');
            
        // PDF des rapports (ancienne méthode)
        Route::post('/export-reports', [ManagerController::class, 'exportReports'])->name('export.reports');
    });
    
    // ==================== ROUTE AJAX POUR VÉRIFICATION STOCK ====================
    Route::get('/check-stock/{fuelType}/{quantity}', function($fuelType, $quantity) {
        $currentStock = \App\Models\StockMovement::currentStock($fuelType);
        $canSell = \App\Models\StockMovement::canSell($fuelType, $quantity);
        
        return response()->json([
            'current_stock' => $currentStock,
            'requested_quantity' => $quantity,
            'can_sell' => $canSell,
            'remaining_after' => $canSell ? $currentStock - $quantity : $currentStock,
            'message' => $canSell ? 
                "Stock suffisant" : 
                "Stock insuffisant. Disponible: {$currentStock} L, Demandé: {$quantity} L"
        ]);
    })->name('check.stock');
    
    // Routes Chief (ou Chargé des Opérations)
    Route::middleware(['role:chief|charge-operations', 'checkStation'])->prefix('chief')->name('chief.')->group(function () {
        // Page principale pour chief : validations
        Route::get('/validations', [ChiefController::class, 'validations'])->name('validations');
        Route::get('/validation/{id}', [ChiefController::class, 'showValidation'])->name('validation.show');
        Route::post('/validation/{id}/valider', [ChiefController::class, 'validerSaisie'])->name('validation.valider');
        Route::post('/validation/{id}/rejeter', [ChiefController::class, 'rejeterSaisie'])->name('validation.rejeter');
        
        // Rapports
        Route::get('/rapports/stations', [ChiefController::class, 'rapportsStations'])->name('rapports.stations');
        Route::get('/rapports/pompistes', [ChiefController::class, 'analysePompistes'])->name('rapports.pompistes');
        Route::get('/rapports/pdf', [ChiefController::class, 'genererRapportPDF'])->name('rapports.pdf');
        
        // Gestion des stations
        Route::get('/stations', [ChiefController::class, 'stations'])->name('stations');
        Route::get('/stations/{id}', [ChiefController::class, 'showStation'])->name('stations.show');
        
        // Gestion des utilisateurs
        Route::get('/utilisateurs', [ChiefController::class, 'utilisateurs'])->name('utilisateurs');
    });

    // Routes Administrateur
    Route::middleware(['role:administrateur'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });
});
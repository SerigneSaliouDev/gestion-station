<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\ShiftSaisie;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Afficher le formulaire d'enregistrement de vente
     */
    public function create()
    {
        $fuelTypes = [
            'super' => 'Super',
            'gazole' => 'Gazole'
        ];
        
        $paymentMethods = [
            'cash' => 'Espèces',
            'card' => 'Carte bancaire',
            'mobile_money' => 'Mobile Money',
            'credit' => 'Crédit'
        ];
        
        $customerTypes = [
            'retail' => 'Détail',
            'wholesale' => 'Gros',
            'corporate' => 'Entreprise'
        ];
        
        // Récupérer les shifts en cours pour l'utilisateur
        $currentShifts = ShiftSaisie::where('user_id', Auth::id())
            ->where('statut', 'en_attente')
            ->orderBy('date_shift', 'desc')
            ->get();
            
        // Récupérer les stocks actuels
        $currentStocks = [];
        foreach ($fuelTypes as $key => $name) {
            $currentStocks[$key] = StockMovement::currentStock($key);
        }
        
        return view('manager.sales.create', compact(
            'fuelTypes', 
            'paymentMethods', 
            'customerTypes',
            'currentShifts',
            'currentStocks'
        ));
    }
    
    /**
     * Enregistrer une vente
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'sale_date' => 'required|date',
            'fuel_type' => 'required|string|in:super,gazole',
            'quantity' => 'required|numeric|min:0.1|max:10000',
            'unit_price' => 'required|numeric|min:0',
            'pump_number' => 'nullable|string|max:50',
            'payment_method' => 'required|string|in:cash,card,mobile_money,credit',
            'customer_type' => 'nullable|string|in:retail,wholesale,corporate',
            'shift_id' => 'nullable|exists:shift_saisies,id',
            'customer_name' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500'
        ]);
        
        // Calculer le montant total
        $data['total_amount'] = $data['quantity'] * $data['unit_price'];
        $data['recorded_by'] = Auth::id();
        
        DB::beginTransaction();
        
        try {
            // Enregistrer la vente et mettre à jour le stock
            $result = Sale::recordSale($data);
            
            DB::commit();
            
            // Récupérer le stock mis à jour
            $updatedStock = StockMovement::currentStock($data['fuel_type']);
            
            return redirect()->route('manager.sales.index')
                ->with('success', "Vente de {$data['quantity']} L de {$data['fuel_type']} enregistrée avec succès!")
                ->with('info', "Stock restant: {$updatedStock} L");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement de la vente: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher la liste des ventes
     */
    public function index(Request $request)
    {
        $query = Sale::with(['shift', 'recorder'])
            ->orderBy('sale_date', 'desc')
            ->orderBy('created_at', 'desc');
            
        // Filtres
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }
        
        if ($request->filled('start_date')) {
            $query->where('sale_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('sale_date', '<=', $request->end_date);
        }
        
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        $sales = $query->paginate(20);
        
        $fuelTypes = [
            'super' => 'Super',
            'gazole' => 'Gazole'
        ];
        
        $paymentMethods = [
            'cash' => 'Espèces',
            'card' => 'Carte bancaire',
            'mobile_money' => 'Mobile Money',
            'credit' => 'Crédit'
        ];
        
        // Statistiques
        $stats = [
            'total_sales' => $sales->count(),
            'total_quantity' => $sales->sum('quantity'),
            'total_amount' => $sales->sum('total_amount'),
            'avg_quantity' => $sales->avg('quantity'),
            'avg_amount' => $sales->avg('total_amount')
        ];
        
        return view('manager.sales.index', compact(
            'sales', 
            'fuelTypes', 
            'paymentMethods',
            'stats',
            'request'
        ));
    }
    
    /**
     * Afficher le détail d'une vente
     */
    public function show($id)
    {
        $sale = Sale::with(['shift', 'recorder'])->findOrFail($id);
        
        return view('manager.sales.show', compact('sale'));
    }
    
    /**
     * Annuler une vente (et restaurer le stock)
     */
    public function cancel($id)
    {
        $sale = Sale::findOrFail($id);
        
        // Vérifier les permissions
        if ($sale->recorded_by != Auth::id() && !Auth::user()->isOperationsManager()) {
            abort(403, 'Non autorisé');
        }
        
        DB::beginTransaction();
        
        try {
            // 1. Restaurer le stock
            $currentStock = StockMovement::currentStock($sale->fuel_type);
            $restoredStock = $currentStock + $sale->quantity;
            
            // 2. Enregistrer l'annulation comme ajustement
            StockMovement::create([
                'movement_date' => now(),
                'fuel_type' => $sale->fuel_type,
                'movement_type' => 'annulation_vente',
                'quantity' => $sale->quantity, // Positif pour ajouter au stock
                'unit_price' => $sale->unit_price,
                'total_amount' => $sale->total_amount,
                'stock_before' => $currentStock,
                'stock_after' => $restoredStock,
                'notes' => "Annulation de la vente #{$sale->id}",
                'recorded_by' => Auth::id()
            ]);
            
            // 3. Marquer la vente comme annulée
            $sale->update([
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => 'Annulation manuelle'
            ]);
            
            DB::commit();
            
            return redirect()->route('manager.sales.index')
                ->with('success', "Vente #{$sale->id} annulée avec succès. Stock restauré: {$sale->quantity} L")
                ->with('info', "Nouveau stock: {$restoredStock} L");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }
    }
    
    /**
     * Rapport des ventes
     */
    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $fuelType = $request->input('fuel_type');
        
        $query = Sale::with('recorder')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->whereNull('cancelled_at');
            
        if ($fuelType) {
            $query->where('fuel_type', $fuelType);
        }
        
        $sales = $query->orderBy('sale_date', 'asc')->get();
        
        // Statistiques détaillées
        $stats = [
            'total_sales' => $sales->count(),
            'total_quantity' => $sales->sum('quantity'),
            'total_amount' => $sales->sum('total_amount'),
            'by_payment_method' => $sales->groupBy('payment_method')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'amount' => $group->sum('total_amount')
                ];
            }),
            'by_customer_type' => $sales->groupBy('customer_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'amount' => $group->sum('total_amount')
                ];
            }),
            'daily_totals' => $sales->groupBy(function($sale) {
                return $sale->sale_date->format('Y-m-d');
            })->map(function($group, $date) {
                return [
                    'date' => $date,
                    'date_formatted' => Carbon::parse($date)->format('d/m/Y'),
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'amount' => $group->sum('total_amount')
                ];
            })->sortBy('date')->values()
        ];
        
        $fuelTypes = [
            'super' => 'Super',
            'gazole' => 'Gazole'
        ];
        
        return view('manager.sales.report', compact(
            'sales',
            'stats',
            'fuelTypes',
            'startDate',
            'endDate',
            'fuelType'
        ));
    }
}
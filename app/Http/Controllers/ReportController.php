<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Shift;
use App\Models\FuelMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    public function dashboard()
    {
        // Période : mois en cours
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        
        // Ventes consolidées
        $consolidatedSales = Shift::select(
                DB::raw('SUM(total_ventes) as total_sales'),
                DB::raw('SUM(total_litres) as total_litres'),
                DB::raw('AVG(ecart_final) as avg_ecart'),
                DB::raw('COUNT(*) as shift_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();
        
        // Ventes par station
        $salesByStation = Shift::select(
                'station_id',
                DB::raw('SUM(total_ventes) as sales'),
                DB::raw('SUM(total_litres) as litres'),
                DB::raw('AVG(ecart_final) as avg_ecart')
            )
            ->with('station')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('station_id')
            ->orderBy('sales', 'desc')
            ->get();
        
        // Évolution des ventes sur 30 jours
        $salesTrend = $this->getSalesTrend(30);
        
        // Stocks globaux
        $globalStocks = DB::table('stocks')
            ->select('fuel_type', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('fuel_type')
            ->get()
            ->pluck('total_quantity', 'fuel_type');
        
        return view('admin.reports.dashboard', compact(
            'consolidatedSales',
            'salesByStation',
            'salesTrend',
            'globalStocks',
            'startDate',
            'endDate'
        ));
    }
    
    public function consolidated(Request $request)
    {
        $period = $request->get('period', 'month');
        
        switch ($period) {
            case 'day':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'quarter':
                $startDate = now()->startOfQuarter();
                $endDate = now()->endOfQuarter();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default:
                $startDate = Carbon::parse($request->date_from) ?? now()->startOfMonth();
                $endDate = Carbon::parse($request->date_to) ?? now()->endOfMonth();
        }
        
        // Données consolidées détaillées
        $consolidatedData = $this->getConsolidatedData($startDate, $endDate);
        
        return view('admin.reports.consolidated', compact(
            'consolidatedData',
            'startDate',
            'endDate',
            'period'
        ));
    }
    
    public function stationComparison(Request $request)
    {
        $stations = Station::with('manager')->get();
        
        $selectedStations = $request->get('stations', $stations->pluck('id')->take(3)->toArray());
        $period = $request->get('period', 'month');
        
        $startDate = $this->getStartDateByPeriod($period);
        $endDate = now();
        
        $comparisonData = [];
        foreach ($selectedStations as $stationId) {
            $station = Station::find($stationId);
            if ($station) {
                $comparisonData[] = [
                    'station' => $station,
                    'metrics' => $this->getStationMetrics($stationId, $startDate, $endDate),
                ];
            }
        }
        
        // KPI globaux pour comparaison
        $globalMetrics = [
            'avg_sales_per_station' => collect($comparisonData)->avg('metrics.total_sales'),
            'avg_litres_per_station' => collect($comparisonData)->avg('metrics.total_litres'),
            'avg_ecart_per_station' => collect($comparisonData)->avg('metrics.avg_ecart'),
        ];
        
        return view('admin.reports.station-comparison', compact(
            'stations',
            'comparisonData',
            'globalMetrics',
            'selectedStations',
            'startDate',
            'endDate',
            'period'
        ));
    }
    
    public function accountingExport()
    {
        $stations = Station::all();
        $fuelTypes = ['super', 'gasoil'];
        
        return view('admin.reports.accounting-export', compact('stations', 'fuelTypes'));
    }
    
    public function exportData(Request $request)
    {
        $request->validate([
            'export_type' => 'required|in:shifts,movements,expenses,all',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'station_id' => 'nullable|exists:stations,id',
            'format' => 'required|in:csv,excel,pdf',
        ]);
        
        $startDate = Carbon::parse($request->date_from);
        $endDate = Carbon::parse($request->date_to);
        
        $data = [];
        $filename = "export_odysee_{$request->export_type}_" . now()->format('Ymd_His');
        
        switch ($request->export_type) {
            case 'shifts':
                $data = $this->exportShiftsData($startDate, $endDate, $request->station_id);
                break;
            case 'movements':
                $data = $this->exportMovementsData($startDate, $endDate, $request->station_id);
                break;
            case 'expenses':
                $data = $this->exportExpensesData($startDate, $endDate, $request->station_id);
                break;
            case 'all':
                $data = $this->exportAllData($startDate, $endDate, $request->station_id);
                break;
        }
        
        // Export selon le format demandé
        if ($request->format === 'csv') {
            return $this->exportToCsv($data, $filename);
        } elseif ($request->format === 'excel') {
            return $this->exportToExcel($data, $filename);
        } else {
            return $this->exportToPdf($data, $filename);
        }
    }
    
    // Méthodes helper privées
    private function getSalesTrend($days = 30)
    {
        $endDate = now();
        $startDate = now()->subDays($days);
        
        $period = CarbonPeriod::create($startDate, $endDate);
        
        $trend = [];
        foreach ($period as $date) {
            $sales = Shift::whereDate('created_at', $date)
                ->sum('total_ventes');
                
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'sales' => $sales,
            ];
        }
        
        return $trend;
    }
    
    private function getConsolidatedData($startDate, $endDate)
    {
        return [
            'sales' => Shift::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('SUM(total_ventes) as total'),
                    DB::raw('AVG(total_ventes) as average'),
                    DB::raw('COUNT(*) as count')
                )->first(),
            'movements' => FuelMovement::whereBetween('movement_date', [$startDate, $endDate])
                ->select(
                    DB::raw('SUM(CASE WHEN movement_type = "reception" THEN quantity ELSE 0 END) as receptions'),
                    DB::raw('SUM(CASE WHEN movement_type = "vente" THEN quantity ELSE 0 END) as sales'),
                    DB::raw('SUM(CASE WHEN movement_type = "ajustement" THEN quantity ELSE 0 END) as adjustments')
                )->first(),
            'expenses' => DB::table('expenses')
                ->whereBetween('date', [$startDate, $endDate])
                ->select(
                    DB::raw('SUM(amount) as total'),
                    DB::raw('AVG(amount) as average'),
                    DB::raw('COUNT(*) as count')
                )->first(),
        ];
    }
    
    private function getStationMetrics($stationId, $startDate, $endDate)
    {
        return Shift::where('station_id', $stationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(total_ventes) as total_sales'),
                DB::raw('SUM(total_litres) as total_litres'),
                DB::raw('AVG(ecart_final) as avg_ecart'),
                DB::raw('COUNT(*) as shift_count'),
                DB::raw('SUM(total_depenses) as total_expenses')
            )->first()->toArray();
    }
    
    private function getStartDateByPeriod($period)
    {
        switch ($period) {
            case 'day': return now()->startOfDay();
            case 'week': return now()->startOfWeek();
            case 'month': return now()->startOfMonth();
            case 'quarter': return now()->startOfQuarter();
            case 'year': return now()->startOfYear();
            default: return now()->subMonth();
        }
    }
    
    // Méthodes d'export (simplifiées)
    private function exportShiftsData($startDate, $endDate, $stationId = null)
    {
        $query = Shift::with('station')
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        return $query->get()->map(function($shift) {
            return [
                'Date' => $shift->date_shift->format('d/m/Y'),
                'Station' => $shift->station->nom,
                'Shift' => $shift->shift,
                'Responsable' => $shift->responsable,
                'Ventes (FCFA)' => $shift->total_ventes,
                'Volume (L)' => $shift->total_litres,
                'Dépenses (FCFA)' => $shift->total_depenses,
                'Écart (FCFA)' => $shift->ecart_final,
                'Statut' => $shift->statut,
            ];
        });
    }
    
    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // En-têtes
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
            }
            
            // Données
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
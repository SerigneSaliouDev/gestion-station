@extends('layouts.admin')

@section('title', 'Comparaison Stations')
@section('page-title', 'Comparaison des Stations')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.dashboard') }}">Reporting</a></li>
    <li class="breadcrumb-item active">Comparaison Stations</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtres -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtres de comparaison</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="comparisonForm" method="GET" action="{{ route('admin.reports.station.comparison') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date de début</label>
                                    <input type="date" 
                                           name="start_date" 
                                           class="form-control"
                                           value="{{ $startDate ?? now()->subMonths(3)->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date de fin</label>
                                    <input type="date" 
                                           name="end_date" 
                                           class="form-control"
                                           value="{{ $endDate ?? now()->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Stations à comparer</label>
                                    <select name="stations[]" class="form-control select2" multiple>
                                        @foreach($allStations ?? [] as $station)
                                        <option value="{{ $station->id }}" 
                                            {{ in_array($station->id, $selectedStations ?? []) ? 'selected' : '' }}>
                                            {{ $station->nom }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter mr-2"></i>Filtrer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Résultats de comparaison -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Résultats de la comparaison</h3>
                    <div class="card-tools">
                        @if(!empty($comparisonData) && count($comparisonData) > 0)
                        <button type="button" class="btn btn-success btn-sm" onclick="exportToExcel()">
                            <i class="fas fa-file-excel mr-2"></i>Exporter
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($error) && $error)
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ $error }}
                    </div>
                    @endif
                    
                    <!-- Graphique de comparaison -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Comparaison des ventes</h3>
                                </div>
                                <div class="card-body">
                                    @if(!empty($comparisonData) && count($comparisonData) > 0)
                                    <canvas id="comparisonChart" height="100"></canvas>
                                    @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Aucune donnée disponible pour la comparaison.
                                        <br><small>Vérifiez que les stations ont des shifts validés pour la période sélectionnée.</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tableau de comparaison -->
                    <div class="table-responsive">
                        @php
                            // Récupérer les données réelles du contrôleur
                            $realData = $comparisonData ?? [];
                            
                            // Convertir en collection pour faciliter le traitement
                            $comparisonCollection = collect($realData);
                            
                            if($comparisonCollection->isEmpty() && isset($allStations) && $allStations->count() > 0) {
                                echo '<div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        Aucune donnée trouvée pour la période sélectionnée.
                                        <br><small>Vérifiez que les stations ont des shifts validés entre '.($startDate ?? '').' et '.($endDate ?? '').'</small>
                                      </div>';
                            } else if($comparisonCollection->isNotEmpty()) {
                                // Trier les données
                                $sortedData = $comparisonCollection->sortByDesc('total_sales')->values();
                                
                                // Calculer les totaux et moyennes
                                $totalEssenceSales = $sortedData->sum('essence_sales');
                                $totalGasoilSales = $sortedData->sum('gasoil_sales');
                                $totalSales = $sortedData->sum('total_sales');
                                $totalEssenceQty = $sortedData->sum('essence_qty');
                                $totalGasoilQty = $sortedData->sum('gasoil_qty');
                                $totalQty = $sortedData->sum('total_qty');
                                $avgCaPerShift = $sortedData->avg('ca_per_shift');
                                $avgProductivity = $sortedData->avg('productivity');
                                
                                // Calculer les moyennes pour le tableau
                                $count = $sortedData->count();
                                $avgEssenceSales = $count > 0 ? $totalEssenceSales / $count : 0;
                                $avgGasoilSales = $count > 0 ? $totalGasoilSales / $count : 0;
                                $avgSales = $count > 0 ? $totalSales / $count : 0;
                                $avgEssenceQty = $count > 0 ? $totalEssenceQty / $count : 0;
                                $avgGasoilQty = $count > 0 ? $totalGasoilQty / $count : 0;
                                $avgQty = $count > 0 ? $totalQty / $count : 0;
                        @endphp
                        
                        @if($sortedData->count() > 0)
                        <table class="table table-bordered table-striped" id="comparisonTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th rowspan="2" style="vertical-align: middle;">Station</th>
                                    <th colspan="3" class="text-center">Ventes totales (FCFA)</th>
                                    <th colspan="3" class="text-center">Quantité vendue (L)</th>
                                    <th colspan="3" class="text-center">Performance</th>
                                </tr>
                                <tr>
                                    <th>Essence</th>
                                    <th>Gas-oil</th>
                                    <th>Total</th>
                                    <th>Essence</th>
                                    <th>Gas-oil</th>
                                    <th>Total</th>
                                    <th>CA/Shift</th>
                                    <th>Productivité</th>
                                    <th>Rang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sortedData as $index => $station)
                                @php
                                    // Calculer le CA par shift (CA moyen par shift)
                                    $caPerShift = isset($station['ca_per_shift']) ? $station['ca_per_shift'] : 0;
                                    
                                    // Calculer la productivité (CA par litre)
                                    $productivity = isset($station['productivity']) ? $station['productivity'] : 0;
                                    
                                    // Vérifier si la station a des données
                                    $hasData = ($station['total_sales'] ?? 0) > 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $station['name'] ?? 'N/A' }}</strong>
                                        @if(!empty($station['code']))
                                        <br><small class="text-muted">{{ $station['code'] }}</small>
                                        @endif
                                        @if(!empty($station['manager']))
                                        <br><small class="text-muted"><i class="fas fa-user"></i> {{ $station['manager'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        {{ $hasData ? number_format($station['essence_sales'], 0, ',', ' ') : '-' }}
                                    </td>
                                    <td class="text-right">
                                        {{ $hasData ? number_format($station['gasoil_sales'], 0, ',', ' ') : '-' }}
                                    </td>
                                    <td class="text-right bg-light">
                                        <strong>{{ $hasData ? number_format($station['total_sales'], 0, ',', ' ') : '-' }}</strong>
                                    </td>
                                    <td class="text-right">
                                        {{ $hasData ? number_format($station['essence_qty'], 0, ',', ' ') . ' L' : '-' }}
                                    </td>
                                    <td class="text-right">
                                        {{ $hasData ? number_format($station['gasoil_qty'], 0, ',', ' ') . ' L' : '-' }}
                                    </td>
                                    <td class="text-right bg-light">
                                        <strong>{{ $hasData ? number_format($station['total_qty'], 0, ',', ' ') . ' L' : '-' }}</strong>
                                    </td>
                                    <td class="text-right">
                                        {{ $hasData ? number_format($caPerShift, 0, ',', ' ') . ' FCFA' : '-' }}
                                    </td>
                                    <td class="text-right">
                                        {{ $hasData ? number_format($productivity, 2, ',', ' ') . ' FCFA/L' : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @if($hasData)
                                            @if($index == 0)
                                            <span class="badge badge-success">1er</span>
                                            @elseif($index == 1)
                                            <span class="badge badge-warning">2ème</span>
                                            @elseif($index == 2)
                                            <span class="badge badge-info">3ème</span>
                                            @else
                                            <span class="badge badge-secondary">{{ $index + 1 }}ème</span>
                                            @endif
                                        @else
                                        <span class="badge badge-light">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-dark text-white">
                                <tr>
                                    <td><strong>MOYENNE</strong></td>
                                    <td class="text-right">
                                        {{ number_format($avgEssenceSales, 0, ',', ' ') }}
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($avgGasoilSales, 0, ',', ' ') }}
                                    </td>
                                    <td class="text-right">
                                        <strong>{{ number_format($avgSales, 0, ',', ' ') }}</strong>
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($avgEssenceQty, 0, ',', ' ') }} L
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($avgGasoilQty, 0, ',', ' ') }} L
                                    </td>
                                    <td class="text-right">
                                        <strong>{{ number_format($avgQty, 0, ',', ' ') }} L</strong>
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($avgCaPerShift, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($avgProductivity, 2, ',', ' ') }} FCFA/L
                                    </td>
                                    <td class="text-center">-</td>
                                </tr>
                            </tfoot>
                        </table>
                        @endif
                        
                        <!-- Analyse détaillée -->
                        @if($sortedData->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Répartition par type de carburant</h3>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($fuelDistribution))
                                        <div class="row">
                                            <div class="col-6 text-center">
                                                <h4 class="text-danger">
                                                    {{ number_format($fuelDistribution['essence']['percentage'] ?? 0, 1) }}%
                                                </h4>
                                                <p>Essence</p>
                                                <small class="text-muted">
                                                    {{ number_format($fuelDistribution['essence']['sales'] ?? 0, 0, ',', ' ') }} FCFA
                                                </small>
                                            </div>
                                            <div class="col-6 text-center">
                                                <h4 class="text-warning">
                                                    {{ number_format($fuelDistribution['gasoil']['percentage'] ?? 0, 1) }}%
                                                </h4>
                                                <p>Gas-oil</p>
                                                <small class="text-muted">
                                                    {{ number_format($fuelDistribution['gasoil']['sales'] ?? 0, 0, ',', ' ') }} FCFA
                                                </small>
                                            </div>
                                        </div>
                                        <canvas id="fuelTypeChart" height="200"></canvas>
                                        @else
                                        <p class="text-muted">Aucune donnée de répartition disponible.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            @if($sortedData->count() > 3)
                                            Top 3 des stations
                                            @else
                                            Classement des stations
                                            @endif
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group">
                                            @foreach($sortedData->take(3) as $index => $topStation)
                                            @php
                                                $topStationTotal = $topStation['total_sales'] ?? 0;
                                            @endphp
                                            @if($topStationTotal > 0)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-1">
                                                        {{ $index + 1 }}. {{ $topStation['name'] ?? 'N/A' }}
                                                    </h5>
                                                    <p class="mb-1">
                                                        CA Total: {{ number_format($topStationTotal, 0, ',', ' ') }} FCFA
                                                    </p>
                                                    <small class="text-muted">
                                                        {{ $topStation['shifts_count'] ?? 0 }} shifts | 
                                                        Stock: S{{ number_format($topStation['super_stock'] ?? 0, 0, ',', ' ') }}L/G{{ number_format($topStation['gasoil_stock'] ?? 0, 0, ',', ' ') }}L
                                                    </small>
                                                </div>
                                                <div class="text-right">
                                                    @if($index == 0)
                                                    <span class="badge badge-success badge-pill p-2">
                                                        <i class="fas fa-trophy fa-lg"></i>
                                                    </span>
                                                    @elseif($index == 1)
                                                    <span class="badge badge-warning badge-pill p-2">
                                                        <i class="fas fa-medal fa-lg"></i>
                                                    </span>
                                                    @else
                                                    <span class="badge badge-info badge-pill p-2">
                                                        <i class="fas fa-award fa-lg"></i>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                            @endforeach
                                            
                                            @if($sortedData->where('total_sales', '>', 0)->count() == 0)
                                            <div class="list-group-item text-center text-muted">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                Aucune station avec des ventes enregistrées
                                            </div>
                                            @endif
                                        </div>
                                        
                                        @if($sortedData->where('total_sales', '>', 0)->count() > 0)
                                        <div class="mt-3">
                                            <h5>Analyse :</h5>
                                            <ul class="list-unstyled">
                                                @foreach($sortedData->take(3) as $index => $station)
                                                @php
                                                    $stationEssence = $station['essence_sales'] ?? 0;
                                                    $stationGasoil = $station['gasoil_sales'] ?? 0;
                                                    $stationTotal = $station['total_sales'] ?? 0;
                                                @endphp
                                                @if($stationTotal > 0)
                                                <li>
                                                    @if($index == 0)
                                                    <i class="fas fa-check text-success mr-2"></i>
                                                    @elseif($index == 1)
                                                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                                                    @else
                                                    <i class="fas fa-lightbulb text-info mr-2"></i>
                                                    @endif
                                                    {{ $station['name'] ?? 'Station' }} : 
                                                    @if($stationEssence > $stationGasoil)
                                                    Meilleur sur l'essence
                                                    @elseif($stationGasoil > $stationEssence)
                                                    Meilleur sur le gas-oil
                                                    @else
                                                    Performance équilibrée
                                                    @endif
                                                </li>
                                                @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Statistiques globales -->
                        @if(isset($totalStats) && !empty($totalStats))
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Synthèse globale</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <div class="info-box bg-info">
                                                    <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Ventes Totales</span>
                                                        <span class="info-box-number">
                                                            {{ number_format($totalStats['total_sales'] ?? 0, 0, ',', ' ') }} FCFA
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box bg-success">
                                                    <span class="info-box-icon"><i class="fas fa-oil-can"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Quantité Totale</span>
                                                        <span class="info-box-number">
                                                            {{ number_format($totalStats['total_litres'] ?? 0, 0, ',', ' ') }} L
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box bg-warning">
                                                    <span class="info-box-icon"><i class="fas fa-exchange-alt"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Shifts Totaux</span>
                                                        <span class="info-box-number">
                                                            {{ $totalStats['total_shifts'] ?? 0 }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box bg-primary">
                                                    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">CA moyen/Shift</span>
                                                        <span class="info-box-number">
                                                            {{ number_format($totalStats['avg_sales_per_shift'] ?? 0, 0, ',', ' ') }} FCFA
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @php } @endphp {{-- Fin du if($comparisonCollection->isNotEmpty()) --}}
                    </div>
                </div>
                
                @if(isset($comparisonData) && count($comparisonData) > 0)
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Données basées sur {{ count($comparisonData ?? []) }} station(s) | 
                                Période: {{ $startDate ?? '' }} au {{ $endDate ?? '' }}
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-primary" onclick="printReport()">
                                <i class="fas fa-print mr-2"></i>Imprimer le rapport
                            </button>
                            <button type="button" class="btn btn-danger" onclick="generatePDF()">
                                <i class="fas fa-file-pdf mr-2"></i>Générer PDF
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
    @media print {
        .card-header, .card-footer, .btn, .select2-container, .dataTables_filter, .dataTables_paginate {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        table {
            font-size: 11px;
        }
    }
    .info-box {
        min-height: 80px;
    }
    .info-box-icon {
        height: 80px;
        width: 80px;
        font-size: 40px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Initialiser Select2
        $('.select2').select2({
            placeholder: 'Sélectionnez les stations',
            allowClear: true
        });
        
        // Graphique de comparaison principal
        @if(isset($comparisonData) && count($comparisonData) > 0)
        var ctx = document.getElementById('comparisonChart');
        if (ctx) {
            var stationNames = @json(collect($comparisonData)->pluck('name'));
            var essenceSales = @json(collect($comparisonData)->pluck('essence_sales'));
            var gasoilSales = @json(collect($comparisonData)->pluck('gasoil_sales'));
            
            var comparisonChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: stationNames,
                    datasets: [{
                        label: 'Ventes Essence (FCFA)',
                        data: essenceSales,
                        backgroundColor: '#dc3545',
                        borderColor: '#dc3545',
                        borderWidth: 1
                    }, {
                        label: 'Ventes Gas-oil (FCFA)',
                        data: gasoilSales,
                        backgroundColor: '#ffc107',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return (value / 1000).toFixed(0) + 'K';
                                    }
                                    return value;
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Graphique par type de carburant
        var fuelCtx = document.getElementById('fuelTypeChart');
        if (fuelCtx) {
            @php
                $essencePerc = isset($fuelDistribution['essence']['percentage']) ? $fuelDistribution['essence']['percentage'] : 0;
                $gasoilPerc = isset($fuelDistribution['gasoil']['percentage']) ? $fuelDistribution['gasoil']['percentage'] : 0;
            @endphp
            
            var fuelTypeChart = new Chart(fuelCtx, {
                type: 'pie',
                data: {
                    labels: ['Essence', 'Gas-oil'],
                    datasets: [{
                        data: [{{ $essencePerc }}, {{ $gasoilPerc }}],
                        backgroundColor: ['#dc3545', '#ffc107'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let value = context.raw;
                                    return context.label + ': ' + value.toFixed(1) + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif
    });
    
    function exportToExcel() {
        // Sélectionner le tableau
        var table = document.getElementById('comparisonTable');
        
        if (!table) {
            alert('Aucun tableau à exporter');
            return;
        }
        
        // Créer un nouveau classeur
        var wb = XLSX.utils.book_new();
        
        // Convertir le tableau en feuille de calcul
        var ws = XLSX.utils.table_to_sheet(table);
        
        // Ajouter la feuille au classeur
        XLSX.utils.book_append_sheet(wb, ws, "Comparaison Stations");
        
        // Générer le fichier Excel
        var date = new Date().toISOString().split('T')[0];
        XLSX.writeFile(wb, "comparaison_stations_" + date + ".xlsx");
    }
    
    function printReport() {
        window.print();
    }
    
    function generatePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        // Titre
        doc.setFontSize(20);
        doc.text('Rapport de Comparaison des Stations', 105, 15, null, null, 'center');
        
        // Sous-titre
        doc.setFontSize(12);
        doc.text('Période: {{ $startDate ?? "" }} au {{ $endDate ?? "" }}', 105, 25, null, null, 'center');
        
        // Tableau
        const table = document.getElementById('comparisonTable');
        if (table) {
            html2canvas(table).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const imgWidth = 190;
                const pageHeight = 297;
                const imgHeight = canvas.height * imgWidth / canvas.width;
                
                let heightLeft = imgHeight;
                let position = 40;
                
                doc.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                heightLeft -= pageHeight - 50;
                
                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    doc.addPage();
                    doc.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight - 20;
                }
                
                doc.save('comparaison_stations_{{ $startDate ?? now()->format("Y-m-d") }}.pdf');
            });
        } else {
            alert('Aucun tableau à exporter en PDF');
        }
    }
</script>
@endpush
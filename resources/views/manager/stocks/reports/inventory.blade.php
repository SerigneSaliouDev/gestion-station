@extends('layouts.app')

@section('title', 'Rapport d\'Inventaire des Cuves')
@section('page-title', 'Rapport d\'Inventaire - Jaugeages des Cuves')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.stocks.dashboard') }}">Stocks</a></li>
<li class="breadcrumb-item"><a href="{{ route('manager.stocks.reports.reconciliation') }}">Rapports</a></li>
<li class="breadcrumb-item active">Inventaire Cuves</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtres -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtres de Période</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.stocks.reports.inventory') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Date de début</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Date de fin</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Appliquer les Filtres
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mt-3">
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-ruler"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Mesures</span>
                    <span class="info-box-number">{{ $stats['total_measurements'] }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        Période: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="info-box {{ $stats['average_difference'] > 1.0 ? 'bg-warning' : 'bg-success' }}">
                <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Écart Moyen</span>
                    <span class="info-box-number">{{ number_format($stats['average_difference'], 2) }}%</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ min(100, abs($stats['average_difference']) * 10) }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $stats['average_difference'] > 1.0 ? '⚠ Au-dessus de la tolérance' : '✓ Dans la tolérance' }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="info-box {{ $stats['max_difference'] > 2.0 ? 'bg-danger' : ($stats['max_difference'] > 1.0 ? 'bg-warning' : 'bg-success') }}">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Écart Max</span>
                    <span class="info-box-number">{{ number_format($stats['max_difference'], 2) }}%</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ min(100, abs($stats['max_difference']) * 5) }}%"></div>
                    </div>
                    <span class="progress-description">
                        @if($stats['max_difference'] > 2.0)
                            ⚠ Écart critique
                        @elseif($stats['max_difference'] > 1.0)
                            ⚠ Écart significatif
                        @else
                            ✓ Acceptable
                        @endif
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="info-box {{ $stats['discrepancies'] > 0 ? 'bg-warning' : 'bg-success' }}">
                <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Écarts > 1%</span>
                    <span class="info-box-number">{{ $stats['discrepancies'] }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $stats['total_measurements'] > 0 ? ($stats['discrepancies'] / $stats['total_measurements']) * 100 : 0 }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $stats['total_measurements'] > 0 ? round(($stats['discrepancies'] / $stats['total_measurements']) * 100, 1) : 0 }}% des mesures
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des jaugeages -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Historique des Jaugeages
            </h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ $tankLevels->count() }} jaugeages</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date Mesure</th>
                            <th>Cuve</th>
                            <th>Carburant</th>
                            <th>Niveau (cm)</th>
                            <th>Volume (L)</th>
                            <th>Stock Théorique (L)</th>
                            <th>Différence (L)</th>
                            <th>Écart (%)</th>
                            <th>Température (°C)</th>
                            <th>Mesuré par</th>
                            <th>Observations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tankLevels as $level)
                            @php
                                $diff = $level->difference_percentage;
                                $diffClass = '';
                                $diffIcon = '';
                                
                                if (abs($diff) > 2.0) {
                                    $diffClass = 'danger';
                                    $diffIcon = 'fas fa-exclamation-triangle';
                                } elseif (abs($diff) > 1.0) {
                                    $diffClass = 'warning';
                                    $diffIcon = 'fas fa-exclamation-circle';
                                } else {
                                    $diffClass = 'success';
                                    $diffIcon = 'fas fa-check-circle';
                                }
                            @endphp
                            <tr>
                                <td>
                                    {{ optional($level->measurement_date)->format('d/m/Y') }}
                                    <br><small class="text-muted">{{ optional($level->measurement_date)->format('H:i') }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $level->tank_number }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $level->fuel_type == 'super' ? 'warning' : 'secondary' }}">
                                        {{ ucfirst($level->fuel_type) }}
                                    </span>
                                </td>
                                <td class="text-right">{{ number_format($level->level_cm, 1) }}</td>
                                <td class="text-right">{{ number_format($level->volume_liters, 0, ',', ' ') }}</td>
                                <td class="text-right">{{ number_format($level->theoretical_stock, 0, ',', ' ') }}</td>
                                <td class="text-right {{ $level->difference > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $level->difference > 0 ? '+' : '' }}{{ number_format($level->difference, 0, ',', ' ') }}
                                </td>
                                <td class="text-{{ $diffClass }}">
                                    <i class="{{ $diffIcon }}"></i>
                                    {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}%
                                </td>
                                <td class="text-center">
                                    @if($level->temperature_c)
                                        {{ number_format($level->temperature_c, 1) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($level->measurer)
                                        <small>{{ $level->measurer->name }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($level->observations)
                                        <small class="text-muted">{{ Str::limit($level->observations, 50) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="fas fa-ruler fa-2x mb-2"></i>
                                    <p>Aucun jaugeage trouvé pour cette période</p>
                                    <a href="{{ route('manager.stocks.tank-levels.create') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Effectuer un jaugeage
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <div class="legend">
                        <small>
                            <span class="text-success"><i class="fas fa-check-circle"></i> Écart ≤ 1%</span> |
                            <span class="text-warning"><i class="fas fa-exclamation-circle"></i> Écart 1-2%</span> |
                            <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Écart > 2%</span>
                        </small>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    @if($tankLevels->count() > 0)
                        <a href="javascript:window.print()" class="btn btn-sm btn-secondary">
                            <i class="fas fa-print"></i> Imprimer
                        </a>
                        <a href="{{ route('manager.stocks.tank-levels.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Nouveau Jaugeage
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique des écarts (optionnel) -->
    @if($tankLevels->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Évolution des Écarts
            </h3>
        </div>
        <div class="card-body">
            <canvas id="discrepancyChart" height="100"></canvas>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="row mt-3">
        <div class="col-md-6">
            <a href="{{ route('manager.stocks.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au Tableau de Bord
            </a>
            <a href="{{ route('manager.stocks.reports.reconciliation') }}" class="btn btn-info ml-2">
                <i class="fas fa-exchange-alt"></i> Rapport de Synthese
            </a>
        </div>
        <div class="col-md-6 text-right">
            @if($tankLevels->count() > 0)
                <button type="button" class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Exporter Excel
                </button>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        margin-bottom: 0;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .legend span {
        margin-right: 10px;
    }
</style>
@endpush

@push('scripts')
@if($tankLevels->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des écarts
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('discrepancyChart').getContext('2d');
        
        // Préparer les données
        var labels = @json($tankLevels->map(function($item) {
            return $item->measurement_date->format('d/m');
        }));
        
        var discrepancies = @json($tankLevels->map(function($item) {
            return $item->difference_percentage;
        }));
        
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Écart (%)',
                    data: discrepancies,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Écart: ' + context.parsed.y.toFixed(2) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Écart (%)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date de mesure'
                        }
                    }
                }
            }
        });
    });
    
    // Export Excel
    function exportToExcel() {
        // Simple export - vous pouvez améliorer avec une librairie
        var table = document.querySelector('table');
        var html = table.outerHTML;
        var url = 'data:application/vnd.ms-excel,' + escape(html);
        
        var link = document.createElement('a');
        link.href = url;
        link.download = 'inventaire-cuves-{{ now()->format("Y-m-d") }}.xls';
        link.click();
    }
</script>
@endif
@endpush
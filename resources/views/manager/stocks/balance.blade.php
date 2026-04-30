@extends('layouts.app')

@section('title', 'Bilan de Stock')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">📊 Bilan de Stock</h1>
        <div>
            <a href="{{ route('manager.stocks.history') }}" class="btn btn-info">
                <i class="fas fa-history"></i> Historique
            </a>
            <a href="{{ route('manager.stocks.receptions.create') }}" class="btn btn-primary btn-sm me-2">
                <i class="fas fa-plus"></i> Nouvelle Réception
            </a>
            <a href="{{ route('manager.stocks.dashboard') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    @if(isset($error))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Erreur:</strong> {{ $error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Cartes de résumé -->
    <div class="row mb-4">
        @foreach($balance as $fuelType => $data)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ ucfirst($fuelType) }} (Stock Actuel)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($data['current'], 2) }} L
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-{{ $data['current'] > 10000 ? 'success' : ($data['current'] > 5000 ? 'warning' : 'danger') }}">
                                    @if($data['current'] > 10000)
                                        <i class="fas fa-check-circle"></i> Bon niveau
                                    @elseif($data['current'] > 5000)
                                        <i class="fas fa-exclamation-triangle"></i> Niveau moyen
                                    @else
                                        <i class="fas fa-exclamation-circle"></i> Niveau bas
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gas-pump fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Tableau détaillé -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-chart-line me-2"></i>Détails mensuels par type de carburant
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Type Carburant</th>
                            <th>Stock Actuel (L)</th>
                            <th>Réceptions mensuelles (L)</th>
                            <th>Ventes mensuelles (L)</th>
                            <th>Ajustements mensuels (L)</th>
                            <th>Variation nette (L)</th>
                            <th>Cohérence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balance as $fuelType => $data)
                        <tr>
                            <td>
                                <strong>{{ ucfirst($fuelType) }}</strong>
                            </td>
                            <td class="{{ $data['current'] > 10000 ? 'stock-positive' : ($data['current'] > 5000 ? 'stock-warning' : 'stock-negative') }}">
                                {{ number_format($data['current'], 2) }} L
                            </td>
                            <td class="text-success">
                                +{{ number_format($data['monthly_receptions'], 2) }} L
                            </td>
                            <td class="text-danger">
                                -{{ number_format($data['monthly_sales'], 2) }} L
                            </td>
                            <td>
                                @if($data['monthly_adjustments'] > 0)
                                    <span class="text-success">+{{ number_format($data['monthly_adjustments'], 2) }} L</span>
                                @elseif($data['monthly_adjustments'] < 0)
                                    <span class="text-danger">{{ number_format($data['monthly_adjustments'], 2) }} L</span>
                                @else
                                    <span class="text-muted">0.00 L</span>
                                @endif
                            </td>
                            <td class="{{ $data['net_variation'] > 0 ? 'text-success' : ($data['net_variation'] < 0 ? 'text-danger' : 'text-muted') }}">
                                {{ number_format($data['net_variation'], 2) }} L
                            </td>
                            <td>
                                @if(isset($consistency[$fuelType]['is_consistent']))
                                    @if($consistency[$fuelType]['is_consistent'])
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> OK
                                        </span>
                                    @else
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" 
                                              title="{{ count($consistency[$fuelType]['inconsistencies'] ?? []) }} incohérence(s)">
                                            <i class="fas fa-exclamation-triangle"></i> Problème
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-question-circle"></i> Non vérifié
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Dans la section Graphiques -->


    <!-- Vérification de cohérence -->
    @if(isset($consistency) && !empty($consistency))
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-search me-2"></i>Vérification de cohérence
            </h6>
        </div>
        <div class="card-body">
            @foreach($consistency as $fuelType => $check)
            <div class="mb-3">
                <h6 class="font-weight-bold">{{ ucfirst($fuelType) }}</h6>
                <p>
                    <span class="me-3">
                        <i class="fas fa-database me-1"></i> 
                        Mouvements: {{ $check['movements_count'] ?? 0 }}
                    </span>
                    <span class="me-3">
                        <i class="fas fa-check-circle me-1"></i> 
                        Stock actuel: {{ number_format($check['current_stock'] ?? 0, 2) }} L
                    </span>
                    <span>
                        <i class="fas fa-calculator me-1"></i> 
                        Stock attendu: {{ number_format($check['expected_stock'] ?? 0, 2) }} L
                    </span>
                </p>
                
                @if(isset($check['inconsistencies']) && count($check['inconsistencies']) > 0)
                <div class="alert alert-danger">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Incohérences détectées ({{ count($check['inconsistencies']) }})
                    </h6>
                    <ul class="mb-0">
                        @foreach(array_slice($check['inconsistencies'], 0, 5) as $issue)
                        <li>{{ $issue['issue'] ?? 'Erreur inconnue' }}</li>
                        @endforeach
                        @if(count($check['inconsistencies']) > 5)
                        <li>... et {{ count($check['inconsistencies']) - 5 }} autres</li>
                        @endif
                    </ul>
                </div>
                @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    Aucune incohérence détectée. Les stocks sont cohérents.
                </div>
                @endif
            </div>
            <hr>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé, données disponibles:');
    console.log('Super:', {{ $balance['super']['current'] ?? 0 }});
    console.log('Gazole:', {{ $balance['gazole']['current'] ?? 0 }});
    
    // Vérifier si Chart.js est chargé
    if (typeof Chart === 'undefined') {
        console.error('Chart.js n\'est pas chargé!');
        document.getElementById('stockPieChart').parentElement.innerHTML = 
            '<div class="alert alert-danger">Chart.js non chargé. Rechargez la page.</div>';
        return;
    }
    
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Graphique circulaire de répartition
    var ctxPie = document.getElementById('stockPieChart');
    if (ctxPie) {
        console.log('Création du graphique circulaire...');
        
        // Convertir les valeurs pour éviter les problèmes
        var superValue = parseFloat({{ $balance['super']['current'] ?? 0 }}) || 0;
        var gazoleValue = parseFloat({{ $balance['gazole']['current'] ?? 0 }}) || 0;
        
        // Si les deux valeurs sont 0, afficher un message
        if (superValue === 0 && gazoleValue === 0) {
            ctxPie.parentElement.innerHTML = 
                '<div class="text-center py-4">' +
                '<i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>' +
                '<p class="text-muted">Aucune donnée de stock disponible</p>' +
                '</div>';
            return;
        }
        
        try {
            var stockPieChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ['Super', 'Gazole'],
                    datasets: [{
                        data: [superValue, gazoleValue],
                        backgroundColor: ['#FF6384', '#36A2EB'], // Couleurs plus contrastées
                        hoverBackgroundColor: ['#FF6384', '#36A2EB'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    var value = context.parsed;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    label += value.toLocaleString('fr-FR') + ' L';
                                    label += ' (' + percentage + '%)';
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
            console.log('Graphique circulaire créé avec succès');
        } catch (error) {
            console.error('Erreur création graphique circulaire:', error);
        }
    } else {
        console.error('Élément stockPieChart non trouvé');
    }

    // Graphique à barres des mouvements
    var ctxBar = document.getElementById('monthlyMovementsChart');
    if (ctxBar) {
        console.log('Création du graphique à barres...');
        
        // Récupérer les données
        var superReceptions = parseFloat({{ $balance['super']['monthly_receptions'] ?? 0 }}) || 0;
        var superSales = parseFloat({{ $balance['super']['monthly_sales'] ?? 0 }}) || 0;
        var superAdjustments = parseFloat({{ $balance['super']['monthly_adjustments'] ?? 0 }}) || 0;
        
        var gazoleReceptions = parseFloat({{ $balance['gazole']['monthly_receptions'] ?? 0 }}) || 0;
        var gazoleSales = parseFloat({{ $balance['gazole']['monthly_sales'] ?? 0 }}) || 0;
        var gazoleAdjustments = parseFloat({{ $balance['gazole']['monthly_adjustments'] ?? 0 }}) || 0;
        
        try {
            var monthlyMovementsChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Super', 'Gazole'],
                    datasets: [
                        {
                            label: 'Réceptions',
                            data: [superReceptions, gazoleReceptions],
                            backgroundColor: '#4CAF50',
                            borderColor: '#388E3C',
                            borderWidth: 1
                        },
                        {
                            label: 'Ventes',
                            data: [superSales, gazoleSales],
                            backgroundColor: '#F44336',
                            borderColor: '#D32F2F',
                            borderWidth: 1
                        },
                        {
                            label: 'Ajustements',
                            data: [superAdjustments, gazoleAdjustments],
                            backgroundColor: '#FFC107',
                            borderColor: '#FFA000',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('fr-FR') + ' L';
                                },
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                drawBorder: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    var value = context.parsed.y;
                                    return label + value.toLocaleString('fr-FR') + ' L';
                                }
                            }
                        }
                    }
                }
            });
            console.log('Graphique à barres créé avec succès');
        } catch (error) {
            console.error('Erreur création graphique à barres:', error);
        }
    } else {
        console.error('Élément monthlyMovementsChart non trouvé');
    }
});
</script>

<style>
.stock-positive { color: #28a745; font-weight: bold; }
.stock-negative { color: #dc3545; font-weight: bold; }
.stock-warning { color: #ffc107; font-weight: bold; }
.chart-pie, .chart-bar {
    position: relative;
    height: 300px;
}
</style>
@endsection
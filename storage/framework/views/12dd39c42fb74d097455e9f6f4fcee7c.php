

<?php $__env->startSection('title', 'Bilan de Stock'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">📊 Bilan de Stock</h1>
        <div>
            <a href="<?php echo e(route('manager.stocks.history')); ?>" class="btn btn-info">
                <i class="fas fa-history"></i> Historique
            </a>
            <a href="<?php echo e(route('manager.stocks.receptions.create')); ?>" class="btn btn-primary btn-sm me-2">
                <i class="fas fa-plus"></i> Nouvelle Réception
            </a>
            <a href="<?php echo e(route('manager.stocks.dashboard')); ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php if(isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Erreur:</strong> <?php echo e($error); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Cartes de résumé -->
    <div class="row mb-4">
        <?php $__currentLoopData = $balance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuelType => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?php echo e(ucfirst($fuelType)); ?> (Stock Actuel)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo e(number_format($data['current'], 2)); ?> L
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-<?php echo e($data['current'] > 10000 ? 'success' : ($data['current'] > 5000 ? 'warning' : 'danger')); ?>">
                                    <?php if($data['current'] > 10000): ?>
                                        <i class="fas fa-check-circle"></i> Bon niveau
                                    <?php elseif($data['current'] > 5000): ?>
                                        <i class="fas fa-exclamation-triangle"></i> Niveau moyen
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-circle"></i> Niveau bas
                                    <?php endif; ?>
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
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <?php $__currentLoopData = $balance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuelType => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <strong><?php echo e(ucfirst($fuelType)); ?></strong>
                            </td>
                            <td class="<?php echo e($data['current'] > 10000 ? 'stock-positive' : ($data['current'] > 5000 ? 'stock-warning' : 'stock-negative')); ?>">
                                <?php echo e(number_format($data['current'], 2)); ?> L
                            </td>
                            <td class="text-success">
                                +<?php echo e(number_format($data['monthly_receptions'], 2)); ?> L
                            </td>
                            <td class="text-danger">
                                -<?php echo e(number_format($data['monthly_sales'], 2)); ?> L
                            </td>
                            <td>
                                <?php if($data['monthly_adjustments'] > 0): ?>
                                    <span class="text-success">+<?php echo e(number_format($data['monthly_adjustments'], 2)); ?> L</span>
                                <?php elseif($data['monthly_adjustments'] < 0): ?>
                                    <span class="text-danger"><?php echo e(number_format($data['monthly_adjustments'], 2)); ?> L</span>
                                <?php else: ?>
                                    <span class="text-muted">0.00 L</span>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo e($data['net_variation'] > 0 ? 'text-success' : ($data['net_variation'] < 0 ? 'text-danger' : 'text-muted')); ?>">
                                <?php echo e(number_format($data['net_variation'], 2)); ?> L
                            </td>
                            <td>
                                <?php if(isset($consistency[$fuelType]['is_consistent'])): ?>
                                    <?php if($consistency[$fuelType]['is_consistent']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> OK
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" 
                                              title="<?php echo e(count($consistency[$fuelType]['inconsistencies'] ?? [])); ?> incohérence(s)">
                                            <i class="fas fa-exclamation-triangle"></i> Problème
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-question-circle"></i> Non vérifié
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Dans la section Graphiques -->


    <!-- Vérification de cohérence -->
    <?php if(isset($consistency) && !empty($consistency)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-search me-2"></i>Vérification de cohérence
            </h6>
        </div>
        <div class="card-body">
            <?php $__currentLoopData = $consistency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuelType => $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="mb-3">
                <h6 class="font-weight-bold"><?php echo e(ucfirst($fuelType)); ?></h6>
                <p>
                    <span class="me-3">
                        <i class="fas fa-database me-1"></i> 
                        Mouvements: <?php echo e($check['movements_count'] ?? 0); ?>

                    </span>
                    <span class="me-3">
                        <i class="fas fa-check-circle me-1"></i> 
                        Stock actuel: <?php echo e(number_format($check['current_stock'] ?? 0, 2)); ?> L
                    </span>
                    <span>
                        <i class="fas fa-calculator me-1"></i> 
                        Stock attendu: <?php echo e(number_format($check['expected_stock'] ?? 0, 2)); ?> L
                    </span>
                </p>
                
                <?php if(isset($check['inconsistencies']) && count($check['inconsistencies']) > 0): ?>
                <div class="alert alert-danger">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Incohérences détectées (<?php echo e(count($check['inconsistencies'])); ?>)
                    </h6>
                    <ul class="mb-0">
                        <?php $__currentLoopData = array_slice($check['inconsistencies'], 0, 5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($issue['issue'] ?? 'Erreur inconnue'); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if(count($check['inconsistencies']) > 5): ?>
                        <li>... et <?php echo e(count($check['inconsistencies']) - 5); ?> autres</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    Aucune incohérence détectée. Les stocks sont cohérents.
                </div>
                <?php endif; ?>
            </div>
            <hr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé, données disponibles:');
    console.log('Super:', <?php echo e($balance['super']['current'] ?? 0); ?>);
    console.log('Gazole:', <?php echo e($balance['gazole']['current'] ?? 0); ?>);
    
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
        var superValue = parseFloat(<?php echo e($balance['super']['current'] ?? 0); ?>) || 0;
        var gazoleValue = parseFloat(<?php echo e($balance['gazole']['current'] ?? 0); ?>) || 0;
        
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
        var superReceptions = parseFloat(<?php echo e($balance['super']['monthly_receptions'] ?? 0); ?>) || 0;
        var superSales = parseFloat(<?php echo e($balance['super']['monthly_sales'] ?? 0); ?>) || 0;
        var superAdjustments = parseFloat(<?php echo e($balance['super']['monthly_adjustments'] ?? 0); ?>) || 0;
        
        var gazoleReceptions = parseFloat(<?php echo e($balance['gazole']['monthly_receptions'] ?? 0); ?>) || 0;
        var gazoleSales = parseFloat(<?php echo e($balance['gazole']['monthly_sales'] ?? 0); ?>) || 0;
        var gazoleAdjustments = parseFloat(<?php echo e($balance['gazole']['monthly_adjustments'] ?? 0); ?>) || 0;
        
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/manager/stocks/balance.blade.php ENDPATH**/ ?>
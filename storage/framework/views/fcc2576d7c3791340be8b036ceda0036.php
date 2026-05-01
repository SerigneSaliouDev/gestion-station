

<?php $__env->startSection('title', 'Rapports et Statistiques'); ?>
<?php $__env->startSection('page-title', 'Rapports et Statistiques'); ?>

<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('manager.index_form')); ?>">Manager</a></li>
<li class="breadcrumb-item active">Rapports</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Filtres -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header odyssee-bg-primary">
                <h3 class="card-title text-white">Filtres de Période</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('manager.reports')); ?>" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="periode" class="mr-2">Période:</label>
                        <select name="periode" id="periode" class="form-control" onchange="toggleCustomDays()">
                            <option value="daily" <?php echo e($periode == 'daily' ? 'selected' : ''); ?>>Aujourd'hui</option>
                            <option value="weekly" <?php echo e($periode == 'weekly' ? 'selected' : ''); ?>>7 derniers jours</option>
                            <option value="monthly" <?php echo e($periode == 'monthly' ? 'selected' : ''); ?>>30 derniers jours</option>
                            <option value="custom" <?php echo e($periode == 'custom' ? 'selected' : ''); ?>>Personnalisé</option>
                        </select>
                    </div>
                    
                    <div class="form-group mr-3" id="customDaysContainer" style="display: <?php echo e($periode == 'custom' ? 'block' : 'none'); ?>">
                        <label for="jours" class="mr-2">Nombre de jours:</label>
                        <input type="number" name="jours" id="jours" 
                               class="form-control" min="1" max="365" 
                               value="<?php echo e($jours); ?>" style="width: 80px;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Appliquer
                    </button>
                    
                    <button type="button" onclick="generatePdfReport()" class="btn btn-success ml-2">
                        <i class="fas fa-file-pdf"></i> Générer PDF
                    </button>
                </form>
                
                <div class="mt-2 text-muted">
                    <i class="fas fa-info-circle"></i>
                    Période du <?php echo e(\Carbon\Carbon::parse($startDate)->format('d/m/Y')); ?> 
                    au <?php echo e(\Carbon\Carbon::parse($endDate)->format('d/m/Y')); ?>

                </div>
            </div>
        </div>
    </div>
</div>



<!-- Statistiques Générales -->
<div class="row mt-3">
    <div class="col-md-3">
        <div class="info-box bg-gradient-primary">
            <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Nombre de Shifts</span>
                <span class="info-box-number"><?php echo e($stats['totalShifts']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-gradient-success">
            <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Litres Vendus</span>
                <span class="info-box-number"><?php echo e(number_format($stats['totalLitres'], 2, ',', ' ')); ?> L</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Ventes</span>
                <span class="info-box-number"><?php echo e(number_format($stats['totalVentes'], 0, ',', ' ')); ?> F CFA</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <!-- UTILISATION DES STATS RECALCULÉES -->
        <?php
            $ecartFinalCalcule = $stats['totalEcartFinal'];
            $couleurEcart = $ecartFinalCalcule < 0 ? 'danger' : ($ecartFinalCalcule > 0 ? 'success' : 'secondary');
            $texteEcart = $ecartFinalCalcule < 0 ? 'Manquant' : ($ecartFinalCalcule > 0 ? 'Excédent' : 'Équilibré');
        ?>
        <div class="info-box bg-gradient-<?php echo e($couleurEcart); ?>">
            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Écart Final</span>
                <span class="info-box-number">
                    <?php if($ecartFinalCalcule < 0): ?>
                        <span class="text-danger">-<?php echo e(number_format(abs($ecartFinalCalcule), 0, ',', ' ')); ?></span>
                    <?php elseif($ecartFinalCalcule > 0): ?>
                        <span class="text-success">+<?php echo e(number_format($ecartFinalCalcule, 0, ',', ' ')); ?></span>
                    <?php else: ?>
                        <span class="text-muted"><?php echo e(number_format($ecartFinalCalcule, 0, ',', ' ')); ?></span>
                    <?php endif; ?>
                    F CFA
                    <small class="d-block text-<?php echo e($couleurEcart); ?>">(<?php echo e($texteEcart); ?>)</small>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Analyse des Écarts -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header odyssee-bg-primary">
                <h3 class="card-title text-white">Analyse Détaillée des Écarts</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <?php
                            $ecartInitialCalcule = $stats['totalEcartInitial'];
                            $couleurEcartInitial = $ecartInitialCalcule < 0 ? 'danger' : ($ecartInitialCalcule > 0 ? 'success' : 'secondary');
                        ?>
                        <div class="info-box bg-gradient-info">
                            <span class="info-box-icon"><i class="fas fa-exchange-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Écart Initial Total</span>
                                <span class="info-box-number">
                                    <?php if($ecartInitialCalcule < 0): ?>
                                        <span class="text-danger">-<?php echo e(number_format(abs($ecartInitialCalcule), 0, ',', ' ')); ?></span>
                                    <?php elseif($ecartInitialCalcule > 0): ?>
                                        <span class="text-success">+<?php echo e(number_format($ecartInitialCalcule, 0, ',', ' ')); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><?php echo e(number_format($ecartInitialCalcule, 0, ',', ' ')); ?></span>
                                    <?php endif; ?>
                                    F CFA
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <!-- UTILISATION DES STATS RECALCULÉES -->
                        <?php
                            $ecartFinalCalcule = $stats['totalEcartFinal'];
                            $couleurEcartFinal = $ecartFinalCalcule < 0 ? 'danger' : ($ecartFinalCalcule > 0 ? 'success' : 'secondary');
                        ?>
                        <div class="info-box bg-gradient-<?php echo e($couleurEcartFinal); ?>">
                            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Écart Final Total</span>
                                <span class="info-box-number">
                                    <?php if($ecartFinalCalcule < 0): ?>
                                        <span class="text-danger">-<?php echo e(number_format(abs($ecartFinalCalcule), 0, ',', ' ')); ?></span>
                                    <?php elseif($ecartFinalCalcule > 0): ?>
                                        <span class="text-success">+<?php echo e(number_format($ecartFinalCalcule, 0, ',', ' ')); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><?php echo e(number_format($ecartFinalCalcule, 0, ',', ' ')); ?></span>
                                    <?php endif; ?>
                                    F CFA
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <?php
                            $ecartMoyenCalcule = $stats['ecartMoyen'];
                            $couleurEcartMoyen = $ecartMoyenCalcule < 0 ? 'danger' : ($ecartMoyenCalcule > 0 ? 'success' : 'secondary');
                        ?>
                        <div class="info-box bg-gradient-secondary">
                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Écart Moyen</span>
                                <span class="info-box-number">
                                    <?php if($ecartMoyenCalcule < 0): ?>
                                        <span class="text-danger">-<?php echo e(number_format(abs($ecartMoyenCalcule), 0, ',', ' ')); ?></span>
                                    <?php elseif($ecartMoyenCalcule > 0): ?>
                                        <span class="text-success">+<?php echo e(number_format($ecartMoyenCalcule, 0, ',', ' ')); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><?php echo e(number_format($ecartMoyenCalcule, 0, ',', ' ')); ?></span>
                                    <?php endif; ?>
                                    F CFA
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="info-box bg-gradient-warning">
                            <span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Impact Dépenses</span>
                                <span class="info-box-number text-danger">
                                    - <?php echo e(number_format($stats['totalEcartInitial'] - $stats['totalEcartFinal'], 0, ',', ' ')); ?> F CFA
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Répartition des Écarts -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Répartition des Shifts par Type d'Écart</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Type d'Écart</th>
                                        <th class="text-center">Nombre</th>
                                        <th class="text-center">Pourcentage</th>
                                        <th class="text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="fas fa-minus-circle text-danger mr-2"></i> Manquant (écart négatif)</td>
                                        <td class="text-center"><?php echo e($repartitionEcarts['manquant']); ?></td>
                                        <td class="text-center">
                                            <?php if($stats['totalShifts'] > 0): ?>
                                                <?php echo e(number_format(($repartitionEcarts['manquant'] / $stats['totalShifts']) * 100, 1)); ?>%
                                            <?php else: ?>
                                                0%
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-danger">À surveiller</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-plus-circle text-success mr-2"></i> Excédent (écart positif)</td>
                                        <td class="text-center"><?php echo e($repartitionEcarts['excédent']); ?></td>
                                        <td class="text-center">
                                            <?php if($stats['totalShifts'] > 0): ?>
                                                <?php echo e(number_format(($repartitionEcarts['excédent'] / $stats['totalShifts']) * 100, 1)); ?>%
                                            <?php else: ?>
                                                0%
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success">Bon résultat</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-equals text-secondary mr-2"></i> Équilibré</td>
                                        <td class="text-center"><?php echo e($repartitionEcarts['equilibre']); ?></td>
                                        <td class="text-center">
                                            <?php if($stats['totalShifts'] > 0): ?>
                                                <?php echo e(number_format(($repartitionEcarts['equilibre'] / $stats['totalShifts']) * 100, 1)); ?>%
                                            <?php else: ?>
                                                0%
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-secondary">Neutre</span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th>TOTAL</th>
                                        <th class="text-center"><?php echo e($stats['totalShifts']); ?></th>
                                        <th class="text-center">100%</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Tendance des Écarts</h5>
                        <div class="alert alert-<?php echo e($tendanceEcarts['tendance'] == 'amelioration' ? 'success' : ($tendanceEcarts['tendance'] == 'deterioration' ? 'danger' : 'info')); ?>">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <?php if($tendanceEcarts['tendance'] == 'amelioration'): ?>
                                        <i class="fas fa-chart-line fa-2x text-success"></i>
                                    <?php elseif($tendanceEcarts['tendance'] == 'deterioration'): ?>
                                        <i class="fas fa-chart-line fa-2x text-danger"></i>
                                    <?php else: ?>
                                        <i class="fas fa-chart-line fa-2x text-info"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h5 class="mb-1">
                                        <?php if($tendanceEcarts['tendance'] == 'amelioration'): ?>
                                            <span class="text-success">Tendance positive</span>
                                        <?php elseif($tendanceEcarts['tendance'] == 'deterioration'): ?>
                                            <span class="text-danger">Tendance négative</span>
                                        <?php else: ?>
                                            <span class="text-info">Tendance stable</span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="mb-0"><?php echo e($tendanceEcarts['message']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <h6>Écarts Extrêmes</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-success">Meilleur Écart (Excédent)</h6>
                                        <div class="h4 text-success">
                                            <?php if($stats['ecartMax'] > 0): ?>
                                                +<?php echo e(number_format($stats['ecartMax'], 0, ',', ' ')); ?> F CFA
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-danger">Pire Écart (Manquant)</h6>
                                        <div class="h4 text-danger">
                                            <?php if($stats['ecartMin'] < 0): ?>
                                                <?php echo e(number_format($stats['ecartMin'], 0, ',', ' ')); ?> F CFA
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Évolution des Écarts Journaliers -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>Évolution des Écarts Journaliers</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th class="text-center">Shifts</th>
                                        <th class="text-right">Ventes</th>
                                        <th class="text-right">Versement</th>
                                        <th class="text-right">Dépenses</th>
                                        <th class="text-right">Écart Initial</th>
                                        <th class="text-right">Écart Final</th>
                                        <th class="text-center">Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $ecartsJournaliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        // Calculer les écarts selon la formule correcte
                                        $ecartInitialCalcule = $data['total_versement'] - $data['total_ventes'];
                                        $ecartFinalCalcule = $data['total_versement'] - ($data['total_ventes'] - $data['total_depenses']);
                                    ?>
                                    <tr>
                                        <td class="font-weight-bold"><?php echo e($data['date_format']); ?>/<?php echo e(date('Y')); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo e($data['nombre_shifts']); ?></span>
                                        </td>
                                        <td class="text-right"><?php echo e(number_format($data['total_ventes'], 0, ',', ' ')); ?> F CFA</td>
                                        <td class="text-right"><?php echo e(number_format($data['total_versement'], 0, ',', ' ')); ?> F CFA</td>
                                        <td class="text-right text-danger">- <?php echo e(number_format($data['total_depenses'], 0, ',', ' ')); ?> F CFA</td>
                                        <td class="text-right">
                                            <?php if($ecartInitialCalcule < 0): ?>
                                                <span class="text-danger">-<?php echo e(number_format(abs($ecartInitialCalcule), 0, ',', ' ')); ?> F CFA</span>
                                            <?php elseif($ecartInitialCalcule > 0): ?>
                                                <span class="text-success">+<?php echo e(number_format($ecartInitialCalcule, 0, ',', ' ')); ?> F CFA</span>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e(number_format($ecartInitialCalcule, 0, ',', ' ')); ?> F CFA</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            <?php if($ecartFinalCalcule < 0): ?>
                                                <span class="text-danger">-<?php echo e(number_format(abs($ecartFinalCalcule), 0, ',', ' ')); ?> F CFA</span>
                                                <div class="small text-danger">Manquant</div>
                                            <?php elseif($ecartFinalCalcule > 0): ?>
                                                <span class="text-success">+<?php echo e(number_format($ecartFinalCalcule, 0, ',', ' ')); ?> F CFA</span>
                                                <div class="small text-success">Excédent</div>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e(number_format($ecartFinalCalcule, 0, ',', ' ')); ?> F CFA</span>
                                                <div class="small text-muted">Équilibré</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($ecartFinalCalcule < 0): ?>
                                                <span class="badge badge-danger">Manquant</span>
                                            <?php elseif($ecartFinalCalcule > 0): ?>
                                                <span class="badge badge-success">Excédent</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Équilibré</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                <tfoot class="bg-light">
                                    <?php
                                        // Calculer les totaux selon la formule correcte
                                        $totalEcartInitialCalcule = $stats['totalVersement'] - $stats['totalVentes'];
                                        $totalEcartFinalCalcule = $stats['totalVersement'] - ($stats['totalVentes'] - $stats['totalDepenses']);
                                    ?>
                                    <tr>
                                        <th>TOTAL</th>
                                        <th class="text-center"><?php echo e($stats['totalShifts']); ?></th>
                                        <th class="text-right"><?php echo e(number_format($stats['totalVentes'], 0, ',', ' ')); ?> F CFA</th>
                                        <th class="text-right"><?php echo e(number_format($stats['totalVersement'] ?? 0, 0, ',', ' ')); ?> F CFA</th>
                                        <th class="text-right text-danger">- <?php echo e(number_format($stats['totalDepenses'], 0, ',', ' ')); ?> F CFA</th>
                                        <th class="text-right">
                                            <?php if($totalEcartInitialCalcule < 0): ?>
                                                <span class="text-danger">-<?php echo e(number_format(abs($totalEcartInitialCalcule), 0, ',', ' ')); ?> F CFA</span>
                                            <?php elseif($totalEcartInitialCalcule > 0): ?>
                                                <span class="text-success">+<?php echo e(number_format($totalEcartInitialCalcule, 0, ',', ' ')); ?> F CFA</span>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e(number_format($totalEcartInitialCalcule, 0, ',', ' ')); ?> F CFA</span>
                                            <?php endif; ?>
                                        </th>
                                        <th class="text-right font-weight-bold">
                                            <?php if($totalEcartFinalCalcule < 0): ?>
                                                <span class="text-danger">-<?php echo e(number_format(abs($totalEcartFinalCalcule), 0, ',', ' ')); ?> F CFA</span>
                                            <?php elseif($totalEcartFinalCalcule > 0): ?>
                                                <span class="text-success">+<?php echo e(number_format($totalEcartFinalCalcule, 0, ',', ' ')); ?> F CFA</span>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e(number_format($totalEcartFinalCalcule, 0, ',', ' ')); ?> F CFA</span>
                                            <?php endif; ?>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance par Carburant -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header odyssee-bg-primary">
                <h3 class="card-title text-white">Performance par Type de Carburant</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Répartition des Ventes (Volume)</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Carburant</th>
                                        <th class="text-center">Litres Vendus</th>
                                        <th class="text-center">Pourcentage</th>
                                        <th class="text-center">Pompes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $byFuel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuel => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="font-weight-bold"><?php echo e($fuel); ?></td>
                                        <td class="text-center"><?php echo e(number_format($data['litres'], 2, ',', ' ')); ?> L</td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info" 
                                                     role="progressbar" 
                                                     style="width: <?php echo e($data['pourcentage_litres']); ?>%;"
                                                     aria-valuenow="<?php echo e($data['pourcentage_litres']); ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo e($data['pourcentage_litres']); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php $__currentLoopData = $data['pompes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pompe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="badge badge-secondary"><?php echo e($pompe); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Répartition des Ventes (Montant)</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Carburant</th>
                                        <th class="text-center">Montant des Ventes</th>
                                        <th class="text-center">Pourcentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $byFuel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuel => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="font-weight-bold"><?php echo e($fuel); ?></td>
                                        <td class="text-center text-primary font-weight-bold">
                                            <?php echo e(number_format($data['montant'], 0, ',', ' ')); ?> F CFA
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: <?php echo e($data['pourcentage_montant']); ?>%;"
                                                     aria-valuenow="<?php echo e($data['pourcentage_montant']); ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo e($data['pourcentage_montant']); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th class="text-right">TOTAL:</th>
                                        <th class="text-center"><?php echo e(number_format($stats['totalVentes'], 0, ',', ' ')); ?> F CFA</th>
                                        <th class="text-center">100%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Graphique simple -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>Visualisation des Performances</h5>
                        <div class="row text-center">
                            <?php $__currentLoopData = $byFuel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuel => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo e($fuel); ?></h6>
                                        <div class="h4 text-primary">
                                            <?php echo e(number_format($data['montant'], 0, ',', ' ')); ?> F CFA
                                        </div>
                                        <div class="text-muted">
                                            <?php echo e(number_format($data['litres'], 0, ',', ' ')); ?> L
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge badge-info"><?php echo e($data['pourcentage_montant']); ?>% du total</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Détail des Dépenses -->
<?php if(count($depensesParType) > 0): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header odyssee-bg-primary">
                <h3 class="card-title text-white">Analyse des Dépenses</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php $__currentLoopData = $depensesParType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <i class="<?php echo e($data['icone']); ?> fa-2x text-muted"></i>
                                        <h5 class="mt-2 mb-0"><?php echo e(ucfirst(str_replace('_', ' ', $type))); ?></h5>
                                    </div>
                                    <div class="text-right">
                                        <div class="h4 text-danger">
                                            - <?php echo e(number_format($data['montant'], 0, ',', ' ')); ?> F CFA
                                        </div>
                                        <small><?php echo e($data['nombre']); ?> dépense(s)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Total des dépenses:</strong> 
                    <?php echo e(number_format($stats['totalDepenses'], 0, ',', ' ')); ?> F CFA
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Liste des Shifts -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header odyssee-bg-primary d-flex justify-content-between align-items-center">
                <h3 class="card-title text-white mb-0">Liste des Shifts (<?php echo e($shifts->count()); ?>)</h3>
                <button type="button" onclick="generatePdfReport()" class="btn btn-light btn-sm">
                    <i class="fas fa-file-pdf"></i> Exporter en PDF
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Shift</th>
                                <th>Pompiste</th>
                                <th class="text-right">Litres</th>
                                <th class="text-right">Ventes</th>
                                <th class="text-right">Versement</th>
                                <th class="text-right">Dépenses</th>
                                <th class="text-right">Écart Final</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                // CALCULER L'ÉCART SELON LA FORMULE CORRECTE
                                $ecartFinalCalcule = $shift->versement - ($shift->total_ventes - $shift->total_depenses);
                                $couleurEcart = $ecartFinalCalcule < 0 ? 'danger' : ($ecartFinalCalcule > 0 ? 'success' : 'secondary');
                                $signeEcart = $ecartFinalCalcule >= 0 ? '+' : '';
                            ?>
                            <tr>
                                <td><?php echo e($shift->date_shift->format('d/m/Y')); ?></td>
                                <td><span class="badge badge-info"><?php echo e($shift->shift); ?></span></td>
                                <td><?php echo e($shift->responsable); ?></td>
                                <td class="text-right"><?php echo e(number_format($shift->total_litres, 2, ',', ' ')); ?> L</td>
                                <td class="text-right"><?php echo e(number_format($shift->total_ventes, 0, ',', ' ')); ?> F CFA</td>
                                <td class="text-right"><?php echo e(number_format($shift->versement, 0, ',', ' ')); ?> F CFA</td>
                                <td class="text-right text-danger">- <?php echo e(number_format($shift->total_depenses, 0, ',', ' ')); ?> F CFA</td>
                                <td class="text-right">
                                    <span class="text-<?php echo e($couleurEcart); ?> font-weight-bold">
                                        <?php echo e($signeEcart); ?><?php echo e(number_format(abs($ecartFinalCalcule), 0, ',', ' ')); ?> F CFA
                                    </span>
                                    <?php if($ecartFinalCalcule < 0): ?>
                                        <div class="small text-danger">Manquant</div>
                                    <?php elseif($ecartFinalCalcule > 0): ?>
                                        <div class="small text-success">Excédent</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('manager.history.show', $shift->id)); ?>" 
                                       class="btn btn-sm btn-info" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('manager.saisie.pdf', $shift->id)); ?>" 
                                       class="btn btn-sm btn-success" title="Télécharger PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>
                                    Aucun shift trouvé pour cette période
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire caché pour générer PDF -->
<form id="pdfReportForm" method="POST" action="<?php echo e(route('manager.reports.pdf')); ?>" style="display: none;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="periode" id="pdfPeriode" value="<?php echo e($periode); ?>">
    <input type="hidden" name="jours" id="pdfJours" value="<?php echo e($jours); ?>">
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function toggleCustomDays() {
        const periode = document.getElementById('periode').value;
        const container = document.getElementById('customDaysContainer');
        
        if (periode === 'custom') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }
    
    function generatePdfReport() {
        // Récupérer les valeurs actuelles
        document.getElementById('pdfPeriode').value = document.getElementById('periode').value;
        document.getElementById('pdfJours').value = document.getElementById('jours') ? document.getElementById('jours').value : 7;
        
        // Soumettre le formulaire
        document.getElementById('pdfReportForm').submit();
    }
    
    // Initialiser à l'ouverture de la page
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomDays();
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/manager/reports.blade.php ENDPATH**/ ?>
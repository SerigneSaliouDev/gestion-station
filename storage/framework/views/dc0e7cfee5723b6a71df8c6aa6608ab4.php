

<?php $__env->startSection('title', 'Dashboard Chief'); ?>
<?php $__env->startSection('page-icon', 'fa-tachometer-alt'); ?>
<?php $__env->startSection('page-title', 'Tableau de Bord'); ?>
<?php $__env->startSection('page-subtitle', 'Vue d\'ensemble des opérations - Données en temps réel'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Dashboard</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('stats'); ?>
<!-- Première ligne de stats - 4 cartes uniformes -->
<div class="row">
    <!-- Validations en attente -->
    <div class="col-lg-3 col-6 mb-3 d-flex align-items-stretch">
        <div class="small-box bg-warning stats-card w-100">
            <div class="inner">
                <h3 class="font-weight-bold display-4"><?php echo e($pendingValidationsCount ?? 0); ?></h3>
                <p class="mb-0 text-uppercase font-weight-bold">Validations en attente</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock stats-icon"></i>
            </div>
            <a href="<?php echo e(route('chief.validations')); ?>" class="small-box-footer d-block text-center py-2 bg-dark bg-opacity-25">
                <i class="fas fa-eye mr-1"></i> Voir les détails <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
    
    <!-- Stations actives -->
    <div class="col-lg-3 col-6 mb-3 d-flex align-items-stretch">
        <div class="small-box bg-info stats-card w-100">
            <div class="inner">
                <h3 class="font-weight-bold display-4"><?php echo e($activeStationsCount ?? 0); ?></h3>
                <p class="mb-0 text-uppercase font-weight-bold">Stations actives</p>
            </div>
            <div class="icon">
                <i class="fas fa-gas-pump stats-icon"></i>
            </div>
            <a href="<?php echo e(route('chief.stations')); ?>" class="small-box-footer d-block text-center py-2 bg-dark bg-opacity-25">
                <i class="fas fa-eye mr-1"></i> Voir les stations <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
    
    <!-- Ventes aujourd'hui -->
    <div class="col-lg-3 col-6 mb-3 d-flex align-items-stretch">
        <div class="small-box bg-primary stats-card w-100">
            <div class="inner">
                <h3 class="font-weight-bold display-4"><?php echo e(number_format($todaySales ?? 0, 0, ',', ' ')); ?></h3>
                <p class="mb-0 text-uppercase font-weight-bold">Ventes aujourd'hui</p>
                <small class="text-white-50 font-weight-bold">(FCFA)</small>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line stats-icon"></i>
            </div>
            <a href="<?php echo e(route('chief.rapports.stations')); ?>" class="small-box-footer d-block text-center py-2 bg-dark bg-opacity-25">
                <i class="fas fa-chart-bar mr-1"></i> Voir les rapports <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
    
    <!-- Écart moyen -->
    <div class="col-lg-3 col-6 mb-3 d-flex align-items-stretch">
        <div class="small-box bg-teal stats-card w-100">
            <div class="inner">
                <h3 class="font-weight-bold display-4"><?php echo e(number_format($stats['avg_ecart'] ?? 0, 0, ',', ' ')); ?></h3>
                <p class="mb-0 text-uppercase font-weight-bold">Écart moyen</p>
                <small class="text-white-50 font-weight-bold">(FCFA)</small>
            </div>
            <div class="icon">
                <i class="fas fa-balance-scale stats-icon"></i>
            </div>
            <div class="small-box-footer bg-dark bg-opacity-25 text-center py-2">
                <i class="fas fa-chart-line mr-1"></i> Indicateur clé
            </div>
        </div>
    </div>
</div>

<!-- Deuxième ligne de stats - 3 cartes uniformes -->
<div class="row mt-3">
    <!-- Total shifts aujourd'hui -->
    <div class="col-lg-4 col-md-4 col-sm-6 mb-3 d-flex align-items-stretch">
        <div class="small-box bg-secondary stats-card w-100">
            <div class="inner">
                <h3 class="font-weight-bold display-4"><?php echo e($stats['total_shifts_today'] ?? 0); ?></h3>
                <p class="mb-0 text-uppercase font-weight-bold">Shifts aujourd'hui</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-day stats-icon"></i>
            </div>
            <div class="small-box-footer bg-dark bg-opacity-25 text-center py-2">
                <i class="fas fa-calendar mr-1"></i> Aujourd'hui
            </div>
        </div>
    </div>
    
    <!-- Total shifts ce mois -->
    <div class="col-lg-4 col-md-4 col-sm-6 mb-3 d-flex align-items-stretch">
        <div class="small-box bg-purple stats-card w-100">
            <div class="inner">
                <h3 class="font-weight-bold display-4"><?php echo e($stats['total_shifts_month'] ?? 0); ?></h3>
                <p class="mb-0 text-uppercase font-weight-bold">Shifts ce mois</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt stats-icon"></i>
            </div>
            <div class="small-box-footer bg-dark bg-opacity-25 text-center py-2">
                <i class="fas fa-calendar-alt mr-1"></i> <?php echo e(now()->format('F Y')); ?>

            </div>
        </div>
    </div>
    
    <!-- Total ventes ce mois -->
    <div class="col-lg-4 col-md-4 col-sm-12 mb-3 d-flex align-items-stretch">
        <div class="small-box bg-orange stats-card w-100">
            <div class="inner">
                <h3 class="font-weight-bold display-4"><?php echo e(number_format($stats['total_sales_month'] ?? 0, 0, ',', ' ')); ?></h3>
                <p class="mb-0 text-uppercase font-weight-bold">Ventes ce mois</p>
                <small class="text-white-50 font-weight-bold">(FCFA)</small>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave stats-icon"></i>
            </div>
            <div class="small-box-footer bg-dark bg-opacity-25 text-center py-2">
                <i class="fas fa-chart-line mr-1"></i> Cumul mensuel
            </div>
        </div>
    </div>
</div>

<!-- Troisième ligne : Mini-statistiques par statut -->
<div class="row mt-2 mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex justify-content-center align-items-center flex-wrap">
                    <span class="mr-4 text-muted small">
                        <i class="fas fa-chart-pie mr-1"></i> <strong>Répartition par statut :</strong>
                    </span>
                    <span class="badge badge-warning p-2 mr-2" style="min-width: 120px; font-size: 0.9rem;">
                        <i class="fas fa-clock mr-1"></i> En attente: <strong><?php echo e($shiftsByStatus['en_attente'] ?? 0); ?></strong>
                    </span>
                    <span class="badge badge-success p-2 mr-2" style="min-width: 120px; font-size: 0.9rem;">
                        <i class="fas fa-check mr-1"></i> Validé: <strong><?php echo e($shiftsByStatus['valide'] ?? 0); ?></strong>
                    </span>
                    <span class="badge badge-danger p-2" style="min-width: 120px; font-size: 0.9rem;">
                        <i class="fas fa-times mr-1"></i> Rejeté: <strong><?php echo e($shiftsByStatus['rejete'] ?? 0); ?></strong>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Sélecteur de station -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i> Sélection de la station
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('chief.dashboard')); ?>" id="stationFilterForm">
                    <div class="form-row align-items-center">
                        <div class="col-md-5 mb-2">
                            <label for="station_id" class="mr-2 font-weight-bold">Station à analyser :</label>
                            <select name="station_id" id="station_id" class="form-control" style="min-width: 250px;">
                                <option value="">Toutes les stations (Vue globale)</option>
                                
                                <?php if(isset($allStations) && $allStations->count() > 0): ?>
                                    <?php $__currentLoopData = $allStations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $station): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $isStationA = $station->code == 'A';
                                            $optionClass = $isStationA ? 'font-weight-bold text-warning' : '';
                                            
                                            $isSelected = false;
                                            if (request('station_id') && request('station_id') == $station->id) {
                                                $isSelected = true;
                                            } elseif (isset($selectedStation) && $selectedStation && $selectedStation->id == $station->id) {
                                                $isSelected = true;
                                            }
                                        ?>
                                        <option value="<?php echo e($station->id); ?>" 
                                                <?php echo e($isSelected ? 'selected' : ''); ?>

                                                class="<?php echo e($optionClass); ?>">
                                            <?php echo e($station->nom); ?> (<?php echo e($station->code); ?>)
                                            <?php if($isStationA): ?> 
                                                 ⭐ Station Pilote 
                                            <?php endif; ?>
                                            <?php if($station->manager): ?>
                                                - Manager: <?php echo e($station->manager->name); ?>

                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <option value="" disabled>Aucune station disponible dans le système</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Appliquer le filtre
                            </button>
                            
                            <?php if(request('station_id') || (isset($selectedStation) && $selectedStation)): ?>
                                <a href="<?php echo e(route('chief.dashboard')); ?>" class="btn btn-secondary ml-2">
                                    <i class="fas fa-times"></i> Effacer le filtre
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if(isset($selectedStation) && $selectedStation): ?>
                        <div class="col-md-4 mb-2">
                            <div class="alert alert-info mb-0 p-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-gas-pump mr-2 fa-lg"></i>
                                    <div>
                                        <strong class="d-block"><?php echo e($selectedStation->nom); ?></strong>
                                        <small class="text-muted">
                                            Code: <?php echo e($selectedStation->code); ?> | 
                                            Manager: <?php echo e($selectedStation->manager->name ?? 'Non assigné'); ?>

                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Données spécifiques à la station sélectionnée -->
<?php if(isset($stationSpecificData) && isset($selectedStation) && $selectedStation): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i> 
                    Données détaillées - <?php echo e($selectedStation->nom); ?>

                    <span class="badge badge-light ml-2"><?php echo e($selectedStation->code); ?></span>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3 d-flex align-items-stretch">
                        <div class="info-box bg-primary w-100">
                            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Ventes Aujourd'hui</span>
                                <span class="info-box-number font-weight-bold">
                                    <?php echo e(number_format($stationSpecificData['today_sales'], 0, ',', ' ')); ?>

                                </span>
                                <small>FCFA</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3 d-flex align-items-stretch">
                        <div class="info-box bg-warning w-100">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Validations en Attente</span>
                                <span class="info-box-number font-weight-bold">
                                    <?php echo e($stationSpecificData['pending_validations']); ?>

                                </span>
                                <small>shift(s) à valider</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3 d-flex align-items-stretch">
                        <div class="info-box bg-success w-100">
                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Ventes Ce Mois</span>
                                <span class="info-box-number font-weight-bold">
                                    <?php echo e(number_format($stationSpecificData['total_sales_month'], 0, ',', ' ')); ?>

                                </span>
                                <small>FCFA</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3 d-flex align-items-stretch">
                        <div class="info-box bg-info w-100">
                            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Écart Moyen</span>
                                <span class="info-box-number font-weight-bold">
                                    <?php echo e(number_format($stationSpecificData['avg_ecart'], 0, ',', ' ')); ?>

                                </span>
                                <small>FCFA</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if(isset($stationSpecificData['recent_shifts']) && $stationSpecificData['recent_shifts']->count() > 0): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <h5><i class="fas fa-history mr-2"></i> Derniers Shifts - <?php echo e($selectedStation->nom); ?></h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Shift</th>
                                        <th>Responsable</th>
                                        <th>Ventes</th>
                                        <th>Écart</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $stationSpecificData['recent_shifts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $statusBadge = [
                                                'en_attente' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'En attente'],
                                                'valide' => ['class' => 'success', 'icon' => 'check', 'text' => 'Validé'],
                                                'rejete' => ['class' => 'danger', 'icon' => 'times', 'text' => 'Rejeté'],
                                            ][$shift->statut] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => 'Inconnu'];
                                        ?>
                                        <tr>
                                            <td><?php echo e($shift->date_shift->format('d/m/Y')); ?></td>
                                            <td><?php echo e($shift->shift); ?></td>
                                            <td><?php echo e($shift->responsable); ?></td>
                                            <td class="font-weight-bold">
                                                <?php echo e(number_format($shift->total_ventes, 0, ',', ' ')); ?> F
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo e($shift->ecart_final > 0 ? 'success' : ($shift->ecart_final < 0 ? 'danger' : 'secondary')); ?>">
                                                    <?php echo e(number_format($shift->ecart_final, 0, ',', ' ')); ?> F
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo e($statusBadge['class']); ?>">
                                                    <i class="fas fa-<?php echo e($statusBadge['icon']); ?> mr-1"></i>
                                                    <?php echo e($statusBadge['text']); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo e(route('chief.validation.show', $shift->id)); ?>" 
                                                   class="btn btn-sm btn-outline-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="<?php echo e(route('chief.rapports.stations', ['station_id' => $selectedStation->id])); ?>" 
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-chart-bar mr-1"></i> Rapports détaillés
                </a>
                <a href="<?php echo e(route('chief.stations.show', $selectedStation->id)); ?>" 
                   class="btn btn-info btn-sm ml-2">
                    <i class="fas fa-eye mr-1"></i> Fiche station
                </a>
                <a href="<?php echo e(route('chief.validations', ['station' => $selectedStation->id])); ?>" 
                   class="btn btn-warning btn-sm ml-2">
                    <i class="fas fa-clipboard-check mr-1"></i> Validations en attente
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Station Pilote A -->
<?php if(isset($allStations)): ?>
    <?php
        $stationA = $allStations->firstWhere('code', 'A');
    ?>

    <?php if($stationA && (!isset($selectedStation) || (isset($selectedStation) && $selectedStation->code != 'A'))): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-warning">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-star mr-2"></i> 
                        <strong>Station Pilote A - <?php echo e($stationA->nom); ?></strong>
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">Station prioritaire</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                        $todaySalesA = App\Models\ShiftSaisie::where('station_id', $stationA->id)
                            ->whereDate('date_shift', today())
                            ->where('statut', 'valide')
                            ->sum('total_ventes');
                        
                        $pendingValidationsA = App\Models\ShiftSaisie::where('station_id', $stationA->id)
                            ->where('statut', 'en_attente')
                            ->count();
                        
                        $totalSalesMonthA = App\Models\ShiftSaisie::where('station_id', $stationA->id)
                            ->where('statut', 'valide')
                            ->whereBetween('date_shift', [now()->startOfMonth(), now()])
                            ->sum('total_ventes');
                        
                        $avgEcartA = App\Models\ShiftSaisie::where('station_id', $stationA->id)
                            ->where('statut', 'valide')
                            ->whereBetween('date_shift', [now()->startOfMonth(), now()])
                            ->avg('ecart_final') ?? 0;
                    ?>
                    
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3 d-flex align-items-stretch">
                            <div class="info-box bg-warning w-100">
                                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">VENTES AUJOURD'HUI</span>
                                    <span class="info-box-number font-weight-bold">
                                        <?php echo e(number_format($todaySalesA, 0, ',', ' ')); ?>

                                    </span>
                                    <small>FCFA</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 mb-3 d-flex align-items-stretch">
                            <div class="info-box bg-danger w-100">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">VALIDATIONS EN ATTENTE</span>
                                    <span class="info-box-number font-weight-bold">
                                        <?php echo e($pendingValidationsA); ?>

                                    </span>
                                    <small>shift(s) à valider</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 mb-3 d-flex align-items-stretch">
                            <div class="info-box bg-success w-100">
                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">VENTES CE MOIS</span>
                                    <span class="info-box-number font-weight-bold">
                                        <?php echo e(number_format($totalSalesMonthA, 0, ',', ' ')); ?>

                                    </span>
                                    <small>FCFA</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 mb-3 d-flex align-items-stretch">
                            <div class="info-box bg-info w-100">
                                <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">ÉCART MOYEN</span>
                                    <span class="info-box-number font-weight-bold">
                                        <?php echo e(number_format($avgEcartA, 0, ',', ' ')); ?>

                                    </span>
                                    <small>FCFA</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <a href="<?php echo e(route('chief.dashboard', ['station_id' => $stationA->id])); ?>" 
                               class="btn btn-warning btn-lg px-4 mx-2">
                                <i class="fas fa-tachometer-alt mr-2"></i> Voir dashboard Station A
                            </a>
                            <a href="<?php echo e(route('chief.rapports.stations', ['station_id' => $stationA->id])); ?>" 
                               class="btn btn-primary btn-lg px-4 mx-2">
                                <i class="fas fa-chart-bar mr-2"></i> Rapports détaillés
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Graphique et validations -->
<div class="row">
    <div class="col-lg-8 mb-3 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Ventes des 7 derniers jours
                    <?php if(isset($selectedStation) && $selectedStation): ?>
                        - <?php echo e($selectedStation->nom); ?>

                    <?php endif; ?>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if(isset($salesLast7Days) && count($salesLast7Days) > 0): ?>
                    <div style="position: relative; height: 40vh; min-height: 250px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune donnée de vente disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-3 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-2"></i> Dernières validations en attente
                </h3>
                <div class="card-tools">
                    <span class="badge badge-warning badge-lg"><?php echo e($pendingValidationsCount ?? 0); ?></span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if(isset($recentValidations) && $recentValidations->count() > 0): ?>
                    <ul class="nav flex-column nav-pills">
                        <?php $__currentLoopData = $recentValidations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $validation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="nav-item p-3 border-bottom">
                                <a href="<?php echo e(route('chief.validation.show', $validation->id)); ?>" class="text-decoration-none">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong class="d-block"><?php echo e($validation->station->nom ?? 'Station inconnue'); ?></strong>
                                            <small class="text-muted">
                                                <i class="far fa-calendar mr-1"></i>
                                                <?php echo e($validation->date_shift->format('d/m/Y')); ?> - <?php echo e($validation->shift); ?>

                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-user mr-1"></i>
                                                <?php echo e($validation->user->name ?? $validation->responsable); ?>

                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> En attente
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <p class="text-muted">Aucune validation en attente</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="<?php echo e(route('chief.validations')); ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye mr-1"></i> Voir toutes les validations
                </a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Styles généraux - Uniformisation des cartes */
    .stats-card {
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    
    .stats-icon {
        font-size: 80px;
        opacity: 0.2;
        position: absolute;
        right: 10px;
        bottom: 10px;
    }
    
    .small-box .inner {
        padding: 15px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .small-box .inner h3 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 8px;
        line-height: 1;
    }
    
    .small-box .inner p {
        font-size: 1rem;
        margin-bottom: 0;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .small-box-footer {
        padding: 10px 15px;
        background-color: rgba(0, 0, 0, 0.15);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        font-weight: 600;
        transition: all 0.2s;
        margin-top: auto;
    }
    
    .small-box-footer:hover {
        background-color: rgba(0, 0, 0, 0.25);
        text-decoration: none;
    }
    
    .small-box-footer:hover i {
        transform: translateX(3px);
    }
    
    /* Uniformisation des colonnes */
    .row > [class*="col-"] {
        display: flex;
        flex-direction: column;
    }
    
    /* Couleurs personnalisées */
    .bg-purple {
        background-color: #6f42c1 !important;
        color: white;
    }
    
    .bg-orange {
        background-color: #fd7e14 !important;
        color: white;
    }
    
    .bg-teal {
        background-color: #20c997 !important;
        color: white;
    }
    
    /* Info-box uniformes */
    .info-box {
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s;
        height: 100%;
        min-height: 120px;
        display: flex;
        align-items: stretch;
    }
    
    .info-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .info-box-icon {
        width: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }
    
    .info-box-content {
        padding: 12px 15px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .info-box-text {
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    
    .info-box-number {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 5px;
    }
    
    /* Badges */
    .badge {
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
    }
    
    .badge-lg {
        padding: 8px 15px;
        font-size: 0.9rem;
    }
    
    /* Boutons */
    .btn-lg {
        padding: 10px 24px;
        font-weight: 600;
        border-radius: 8px;
    }
    
    /* Classes utilitaires */
    .bg-light-warning {
        background-color: #fff3cd !important;
    }
    
    .bg-light-primary {
        background-color: #e3f2fd !important;
    }
    
    .bg-opacity-25 {
        background-color: rgba(0, 0, 0, 0.2) !important;
    }
    
    .text-white-50 {
        color: rgba(255, 255, 255, 0.9) !important;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .display-4 {
        font-size: 2.5rem;
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .stats-card, .info-box {
        animation: fadeInUp 0.5s ease-out;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .info-box-icon {
            width: 60px;
            font-size: 24px;
        }
        
        .info-box-number {
            font-size: 1.4rem;
        }
        
        .stats-icon {
            font-size: 60px;
        }
        
        .small-box .inner h3 {
            font-size: 1.8rem;
        }
        
        .stats-card {
            min-height: 150px;
        }
        
        .display-4 {
            font-size: 1.8rem;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    <?php if(isset($salesLast7Days) && count($salesLast7Days) > 0): ?>
    // Graphique des ventes
    const ctx = document.getElementById('salesChart').getContext('2d');
    const dates = <?php echo json_encode(array_column($salesLast7Days, 'date'), 512) ?>;
    const totalSales = <?php echo json_encode(array_column($salesLast7Days, 'total_sales'), 512) ?>;
    const totalLitres = <?php echo json_encode(array_column($salesLast7Days, 'total_litres'), 512) ?>;
    
    const totalVentes = totalSales.reduce((a, b) => a + b, 0);
    const totalVolume = totalLitres.reduce((a, b) => a + b, 0);
    const moyenneVentes = totalVentes / 7;
    const moyenneVolume = totalVolume / 7;
    
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Ventes totales (FCFA)',
                data: totalSales,
                backgroundColor: '#007bff',
                borderColor: '#0056b3',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toLocaleString('fr-FR') + ' FCFA';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return (value/1000000).toFixed(1) + 'M';
                            if (value >= 1000) return (value/1000).toFixed(0) + 'K';
                            return value;
                        },
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
    
    // Ajouter le résumé
    $('#salesChart').closest('.card-body').append(`
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-1 text-muted">Total 7 jours</h6>
                        <h4 class="mb-0 text-primary">${(totalVentes/1000000).toFixed(2)}M FCFA</h4>
                        <small class="text-muted">${totalVolume.toLocaleString('fr-FR')} Litres</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-1 text-muted">Moyenne/jour</h6>
                        <h4 class="mb-0 text-success">${Math.round(moyenneVentes).toLocaleString('fr-FR')} FCFA</h4>
                        <small class="text-muted">${Math.round(moyenneVolume).toLocaleString('fr-FR')} Litres/jour</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-2 text-center">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> 
                Période: ${dates[0]} au ${dates[dates.length-1]}
            </small>
        </div>
    `);
    
    // Redimensionnement
    $(window).on('resize', function() {
        if (typeof salesChart !== 'undefined') {
            salesChart.resize();
        }
    });
    <?php endif; ?>
    
    // Recherche stations
    $('#stationSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#stationsTable tbody tr').each(function() {
            var stationName = $(this).data('station-name') || '';
            $(this).toggle(stationName.indexOf(value) > -1);
        });
    });
    
    // Soumission filtre station
    $('#station_id').on('change', function() {
        if ($(this).val()) {
            $('#stationFilterForm').submit();
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.chief', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/chief/dashboard.blade.php ENDPATH**/ ?>
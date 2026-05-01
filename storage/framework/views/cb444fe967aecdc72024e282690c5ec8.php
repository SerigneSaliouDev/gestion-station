 

<?php $__env->startSection('title', 'Tableau de Bord des Stocks'); ?>

<?php $__env->startSection('content'); ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-gas-pump text-primary"></i> Tableau de Bord des Stocks
            </h1>
            <p class="text-muted mb-0">Vue d'ensemble du stock et des mouvements</p>
        </div>
        <div class="btn-group" role="group">
            <a href="<?php echo e(route('manager.stocks.receptions.create')); ?>" class="btn btn-success">
                <i class="fas fa-truck-loading"></i> Nouvelle Réception
                 </a>
        <!-- CORRIGEZ CE LIEN : manager.tanks.create -->
            <a href="<?php echo e(route('manager.tanks.create')); ?>" class="btn btn-warning">
                <i class="fas fa-oil-can"></i> Créer une cuve
            </a>
            <a href="<?php echo e(route('manager.stocks.history')); ?>" class="btn btn-info">
                <i class="fas fa-history"></i> Historique
            </a>
            
        </div>
    </div>

    <!-- Messages flash avec animation -->
    <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if(session('alert')): ?>
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo e(session('alert')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

            <!-- Section 1: Cartes de synthèse des stocks -->
            <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-chart-pie me-2"></i> Synthèse par Carburant
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <?php $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeKey => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($data['physical_stock'] !== null): ?> 
                <?php
                    // Données DIRECTES du jaugeage
                    $theoretical = $data['theoretical_stock'] ?? 0;
                    $physical = $data['physical_stock'];
                    $differenceLiters = $data['difference_liters'];
                    $differencePermille = $data['difference_per_mille']; // DÉJÀ en ‰
                    $tolerance = $data['tolerance_threshold'] ?? 5; // en ‰
                    
                    // Statut basé sur le jaugeage
                    $isAcceptable = $data['is_acceptable'] ?? true;
                    $statusClass = $isAcceptable ? 'success' : 'danger';
                    $statusIcon = $isAcceptable ? 'fa-check-circle' : 'fa-times-circle';
                    
                    // Pourcentage de remplissage
                    $totalCapacity = $tanks->where('fuel_type', $typeKey)->sum('capacity');
                    $fillPercentage = $totalCapacity > 0 ? ($physical / $totalCapacity) * 100 : 0;
                ?>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card shadow-sm h-100 border-start border-start-<?php echo e($statusClass); ?> border-start-3">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas <?php echo e($typeKey == 'super' ? 'fa-gas-pump text-warning' : 'fa-oil-can text-success'); ?>"></i>
                                    <?php echo e(strtoupper($typeKey)); ?>

                                </h6>
                                <span class="badge bg-<?php echo e($statusClass); ?>">
                                    <i class="fas <?php echo e($statusIcon); ?>"></i>
                                    <?php echo e($isAcceptable ? 'Conforme' : 'Non conforme'); ?>

                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <!-- Théorique vs Physique -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">Théorique</small>
                                    <strong><?php echo e(number_format($theoretical, 0, ',', ' ')); ?> L</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">Physique</small>
                                    <strong><?php echo e(number_format($physical, 0, ',', ' ')); ?> L</strong>
                                </div>
                            </div>
                            
                            <!-- ÉCART EN POUR MILLE -->
                            <div class="mb-3">
                                <div class="text-center">
                                    <h4 class="<?php echo e($differencePermille > $tolerance ? 'text-danger' : 'text-success'); ?>">
                                        <?php echo e($differencePermille > 0 ? '+' : ''); ?><?php echo e(number_format($differencePermille, 1)); ?>‰
                                    </h4>
                                    <small class="text-muted">
                                        <?php echo e($differenceLiters > 0 ? '+' : ''); ?><?php echo e(number_format($differenceLiters, 0, ',', ' ')); ?> L
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Remplissage -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Remplissage</small>
                                    <small><?php echo e(round($fillPercentage, 1)); ?>%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php echo e($fillPercentage > 90 ? 'danger' : ($fillPercentage > 25 ? 'success' : 'warning')); ?>" 
                                        style="width: <?php echo e($fillPercentage); ?>%"></div>
                                </div>
                            </div>
                            
                            <!-- Dernier jaugeage -->
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo e($data['last_measurement_date']->format('d/m/Y H:i')); ?>

                                </small>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo e($data['measured_by_name']); ?>

                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>


    <!-- Section 2: Tableau de conformité -->
    <div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i> Rapport de Jaugeages</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Carburant</th>
                                <th class="text-center">Cuve</th>
                                <th class="text-center">Théorique (L)</th>
                                <th class="text-center">Physique (L)</th>
                                <th class="text-center">Écart (L)</th>
                                <th class="text-center">Écart (‰)</th>
                                <th class="text-center">Tolérance (‰)</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeKey => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($data['physical_stock'] !== null): ?>
                                    <?php
                                        $tolerance = $data['tolerance_threshold'] ?? 5;
                                        $isAcceptable = $data['is_acceptable'] ?? true;
                                        
                                        if (!$isAcceptable) {
                                            $status = 'Non conforme';
                                            $statusClass = 'danger';
                                        } elseif (abs($data['difference_per_mille']) > $tolerance) {
                                            $status = 'À vérifier';
                                            $statusClass = 'warning';
                                        } else {
                                            $status = 'Conforme';
                                            $statusClass = 'success';
                                        }
                                    ?>
                                    
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-<?php echo e($typeKey == 'super' ? 'warning' : 'success'); ?>">
                                                <?php echo e(strtoupper($typeKey)); ?>

                                            </span>
                                        </td>
                                        <td class="text-center"><?php echo e($data['tank_number']); ?></td>
                                        <td class="text-center"><?php echo e(number_format($data['theoretical_stock'], 0, ',', ' ')); ?></td>
                                        <td class="text-center"><?php echo e(number_format($data['physical_stock'], 0, ',', ' ')); ?></td>
                                        <td class="text-center <?php echo e($data['difference_liters'] > 0 ? 'text-success' : 'text-danger'); ?>">
                                            <?php echo e($data['difference_liters'] > 0 ? '+' : ''); ?><?php echo e(number_format($data['difference_liters'], 0, ',', ' ')); ?>

                                        </td>
                                        <td class="text-center fw-bold <?php echo e(abs($data['difference_per_mille']) > $tolerance ? 'text-danger' : 'text-success'); ?>">
                                            <?php echo e($data['difference_per_mille'] > 0 ? '+' : ''); ?><?php echo e(number_format($data['difference_per_mille'], 1)); ?>‰
                                        </td>
                                        <td class="text-center"><?php echo e($tolerance); ?>‰</td>
                                        <td class="text-center">
                                            <?php echo e($data['last_measurement_date']->format('d/m/Y H:i')); ?>

                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo e($statusClass); ?>">
                                                <?php echo e($status); ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Section 2: Détails par Cuve -->
<?php if(isset($tanks) && count($tanks) > 0): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-oil-can me-2"></i> Détails des Cuves
                </h5>
                <span class="badge bg-info">
                    <?php echo e(count($tanks)); ?> cuve(s) configurée(s)
                </span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <?php $__currentLoopData = $tanks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $fillPercentage = $tank->capacity > 0 
                                ? (($tank->current_volume ?? 0) / $tank->capacity) * 100 
                                : 0;
                            
                            // Déterminer la couleur
                            if ($fillPercentage > 90) {
                                $progressClass = 'danger';
                                $iconClass = 'text-danger';
                            } elseif ($fillPercentage > 75) {
                                $progressClass = 'warning';
                                $iconClass = 'text-warning';
                            } elseif ($fillPercentage > 25) {
                                $progressClass = 'success';
                                $iconClass = 'text-success';
                            } else {
                                $progressClass = 'info';
                                $iconClass = 'text-info';
                            }
                            
                            // Dernier jaugeage
                            $lastLevel = $tank->latestLevel ?? null;
                        ?>
                        
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-header bg-light py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="fas fa-tank-water <?php echo e($iconClass); ?> me-2"></i>
                                            Cuve <?php echo e($tank->number); ?>

                                        </h6>
                                        <span class="badge bg-<?php echo e($tank->fuel_type == 'super' ? 'warning' : 'success'); ?>">
                                            <?php echo e(strtoupper($tank->fuel_type)); ?>

                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Informations de base -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Capacité totale:</small>
                                            <strong><?php echo e(number_format($tank->capacity, 0, ',', ' ')); ?> L</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Stock actuel:</small>
                                            <strong><?php echo e(number_format($tank->current_volume ?? 0, 0, ',', ' ')); ?> L</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small>Disponible:</small>
                                            <strong class="text-success">
                                                <?php echo e(number_format($tank->capacity - ($tank->current_volume ?? 0), 0, ',', ' ')); ?> L
                                            </strong>
                                        </div>
                                    </div>
                                    
                                    <!-- Barre de progression -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Remplissage</small>
                                            <small><strong><?php echo e(round($fillPercentage, 1)); ?>%</strong></small>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-<?php echo e($progressClass); ?>" 
                                                 style="width: <?php echo e($fillPercentage); ?>%">
                                            </div>
                                        </div>
                                    </div>
                                    
                                  
                                    
                                    <!-- Actions -->
                                    <div class="mt-3">
                                        <div class="btn-group w-100" role="group">
                                            <a href="<?php echo e(route('manager.tank-levels.create')); ?>?tank_id=<?php echo e($tank->id); ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-ruler me-1"></i> Jauger
                                            </a>
                                          
                                        </div>
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
<?php else: ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-oil-can me-2"></i> Détails des Cuves
                </h5>
            </div>
            <div class="card-body text-center py-5">
                <div class="avatar-lg mx-auto mb-3">
                    <div class="avatar-title bg-soft-warning text-warning rounded-circle">
                        <i class="fas fa-oil-can fa-2x"></i>
                    </div>
                </div>
                <h5 class="text-warning">Aucune cuve configurée</h5>
                <p class="text-muted mb-3">Vous devez d'abord créer des cuves pour gérer vos stocks</p>
                <a href="<?php echo e(route('manager.tanks.create')); ?>" class="btn btn-warning">
                    <i class="fas fa-plus me-1"></i> Créer ma première cuve
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

    <!-- Section 3: Alertes et Jaugeages récents -->
     <div class="row mb-4">
    <!-- Alertes -->
        <div class="col-xl-6">
             <div class="card shadow border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Alertes et Risques
                </h5>
            </div>
            <div class="card-body">
                <?php if(count($alerts) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item border-0 px-0 py-2">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <?php if($alert['severity'] == 'danger'): ?>
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-soft-danger text-danger rounded">
                                                    <i class="fas fa-times-circle"></i>
                                                </div>
                                            </div>
                                        <?php elseif($alert['severity'] == 'warning'): ?>
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-soft-warning text-warning rounded">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-soft-info text-info rounded">
                                                    <i class="fas fa-info-circle"></i>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?php echo e($alert['message']); ?></h6>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i> <?php echo e(now()->format('H:i')); ?>

                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-soft-success text-success rounded-circle">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <h5 class="text-success">Aucune alerte</h5>
                        <p class="text-muted">Tous les stocks sont conformes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

        <!-- Jaugeages récents -->
            <div class="col-xl-6">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-chart-bar me-2"></i> Jaugeages Récents
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Cuve</th>
                                        <th>Carburant</th>
                                        <th>Volume</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $latestTankLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-xs">
                                                            <div class="avatar-title bg-soft-info text-info rounded">
                                                                <?php echo e(substr($level->tank_number ?? '?', 0, 2)); ?>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <h6 class="mb-0"><?php echo e($level->tank_number ?? 'N/A'); ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo e($level->fuel_type == 'super' ? 'warning' : 'success'); ?>">
                                                    <?php echo e(strtoupper($level->fuel_type)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <h6 class="mb-0"><?php echo e(number_format($level->volume_liters, 0, ',', ' ')); ?> L</h6>
                                                <?php if($level->difference_percentage): ?>
                                                    <small class="<?php echo e(abs($level->difference_percentage) > 5 ? 'text-danger' : 'text-muted'); ?>">
                                                        <?php echo e($level->difference_percentage > 0 ? '+' : ''); ?><?php echo e(round($level->difference_percentage, 1)); ?>‰
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?php echo e($level->measurement_date->format('d/m/Y')); ?></small>
                                                <br>
                                                <small class="text-muted"><?php echo e($level->measurement_date->format('H:i')); ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p class="mb-0">Aucun jaugeage récent</p>
                                                </div>
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

    <!-- Section 4: Derniers mouvements de stock -->
    <div class="row">
    <div class="col-12">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-exchange-alt me-2"></i> Derniers Mouvements de Stock
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Type</th>
                                <th>Carburant</th>
                                <th class="text-end">Quantité</th>
                                <th class="pe-4">Cuve</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $latestMovements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $isReception = $movement->movement_type === 'reception';
                                    $typeClass = $isReception ? 'success' : 'danger';
                                    $typeIcon = $isReception ? 'fa-arrow-down' : 'fa-arrow-up';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold"><?php echo e($movement->movement_date->format('d/m/Y')); ?></span>
                                            <small class="text-muted"><?php echo e($movement->created_at->format('H:i')); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-<?php echo e($typeClass); ?> text-<?php echo e($typeClass); ?>">
                                            <i class="fas <?php echo e($typeIcon); ?> me-1"></i>
                                            <?php echo e(ucfirst($movement->movement_type)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo e($movement->fuel_type == 'super' ? 'warning' : 'success'); ?>">
                                            <?php echo e(strtoupper($movement->fuel_type)); ?>

                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-<?php echo e($typeClass); ?>">
                                            <?php echo e($isReception ? '+' : '-'); ?><?php echo e(number_format(abs($movement->quantity), 0, ',', ' ')); ?> L
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        <span class="fw-bold"><?php echo e($movement->tank_number ?? 'N/A'); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                                            <h5 class="mb-2">Aucun mouvement récent</h5>
                                        </div>
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


<style>
    .card {
        border-radius: 0.75rem;
        border: none;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
        border-top-left-radius: 0.75rem !important;
        border-top-right-radius: 0.75rem !important;
    }
    
    .border-start-3 {
        border-left-width: 3px !important;
    }
    
    .avatar-sm {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-lg {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-title {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
    
    .bg-soft-success {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }
    
    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .bg-soft-danger {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
    
    .bg-soft-info {
        background-color: rgba(13, 202, 240, 0.1) !important;
    }
    
    .bg-soft-primary {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .table > :not(:first-child) {
        border-top: 0;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.02);
    }
    
    .list-group-item {
        border-left: 0;
        border-right: 0;
    }
    
    .list-group-item:first-child {
        border-top: 0;
        padding-top: 0;
    }
    
    .list-group-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des cartes au chargement
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/manager/stocks/dashboard.blade.php ENDPATH**/ ?>


<?php $__env->startSection('title', 'Rapport de Station'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <?php if(isset($stationId) && $stationId && isset($stations) && $stations->count() > 0): ?>
                            <i class="fas fa-chart-bar mr-2"></i>
                            Rapport détaillé : <strong><?php echo e($stations->first()->nom); ?></strong>
                            <span class="badge badge-secondary ml-2"><?php echo e($stations->first()->code); ?></span>
                        <?php else: ?>
                            <i class="fas fa-list-alt mr-2"></i>
                            Rapports de toutes les stations
                        <?php endif; ?>
                    </h3>
                    <div class="card-tools">
                        <a href="<?php echo e(route('chief.stations')); ?>" class="btn btn-tool">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- SECTION 1: STATISTIQUES GLOBALES -->
                    <?php if(isset($stats) && is_array($stats)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5><i class="fas fa-chart-line mr-2"></i> Statistiques Globales</h5>
                            <div class="row">
                                <!-- Total Ventes -->
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box bg-gradient-info">
                                        <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Ventes Totales</span>
                                            <span class="info-box-number">
                                                <?php echo e(number_format($stats['total_ventes'] ?? 0, 0, ',', ' ')); ?> FCFA
                                            </span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                <?php echo e(number_format($stats['stations_actives'] ?? 0, 0, ',', ' ')); ?> stations actives
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Volume Total -->
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box bg-gradient-success">
                                        <span class="info-box-icon"><i class="fas fa-oil-can"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Volume Total</span>
                                            <span class="info-box-number">
                                                <?php echo e(number_format($stats['volume_total'] ?? 0, 0, ',', ' ')); ?> L
                                            </span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Taux remplissage: <?php echo e(number_format($stats['taux_remplissage'] ?? 0, 1, ',', ' ')); ?>%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Croissance -->
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box bg-gradient-warning">
                                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Croissance</span>
                                            <span class="info-box-number">
                                                <?php echo e(number_format($stats['croissance'] ?? 0, 1, ',', ' ')); ?>%
                                            </span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Moyenne/station: <?php echo e(number_format($stats['moyenne_station'] ?? 0, 0, ',', ' ')); ?> FCFA
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Meilleure Station -->
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box bg-gradient-primary">
                                        <span class="info-box-icon"><i class="fas fa-trophy"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Meilleure Station</span>
                                            <span class="info-box-number">
                                                <?php if(isset($stats['best_station']) && is_string($stats['best_station'])): ?>
                                                    <?php echo e($stats['best_station']); ?>

                                                <?php elseif(isset($stats['best_station']) && is_object($stats['best_station'])): ?>
                                                    <?php echo e($stats['best_station']->nom ?? 'N/A'); ?>

                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                <?php if(isset($stats['best_station_ventes'])): ?>
                                                    <?php echo e(number_format($stats['best_station_ventes'], 0, ',', ' ')); ?> FCFA
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- SECTION 2: FILTRES ET SÉLECTION DE STATION -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-filter mr-2"></i> Filtres de rapport
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="<?php echo e(route('chief.rapports.stations')); ?>">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Date début</label>
                                                    <input type="date" name="start_date" 
                                                           class="form-control" 
                                                           value="<?php echo e($startDate ?? now()->startOfMonth()->format('Y-m-d')); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Date fin</label>
                                                    <input type="date" name="end_date" 
                                                           class="form-control" 
                                                           value="<?php echo e($endDate ?? now()->format('Y-m-d')); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Station</label>
                                                    <select name="station_id" class="form-control">
                                                        <option value="">Toutes les stations</option>
                                                        <?php $__currentLoopData = $allStations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $station): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($station->id); ?>" 
                                                                <?php echo e((isset($stationId) && $stationId == $station->id) ? 'selected' : ''); ?>>
                                                                <?php echo e($station->nom); ?> (<?php echo e($station->code); ?>)
                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-filter"></i> Appliquer les filtres
                                                </button>
                                                <?php if(isset($stationId) || isset($startDate) || isset($endDate)): ?>
                                                    <a href="<?php echo e(route('chief.rapports.stations')); ?>" 
                                                       class="btn btn-secondary ml-2">
                                                        <i class="fas fa-times"></i> Réinitialiser
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Période analysée</span>
                                    <span class="info-box-number">
                                        <?php echo e(\Carbon\Carbon::parse($startDate ?? now()->startOfMonth())->format('d/m/Y')); ?> 
                                        au <?php echo e(\Carbon\Carbon::parse($endDate ?? now())->format('d/m/Y')); ?>

                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        <?php if(isset($stationId) && isset($stations) && $stations->count() > 0): ?>
                                            Filtre : Station <?php echo e($stations->first()->nom); ?>

                                        <?php else: ?>
                                            Vue globale : Toutes les stations
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: PERFORMANCE DES STATIONS -->
                    <?php if(isset($stationPerformances) && count($stationPerformances) > 0): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5><i class="fas fa-ranking-star mr-2"></i> Performance des stations</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Station</th>
                                            <th>Manager</th>
                                            <th>Ventes (FCFA)</th>
                                            <th>Volume (L)</th>
                                            <th>Nombre Shifts</th>
                                            <th>Écart Moyen</th>
                                            <th>Pourcentage</th>
                                            <th>Performance</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $stationPerformances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $performance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $station = $performance['station'];
                                                $isStationA = $station->code == 'A';
                                                $isSelected = isset($stationId) && $stationId == $station->id;
                                                
                                                // Calculer le score de performance
                                                $score = $performance['performance'] ?? 'moyenne';
                                                $scoreColors = [
                                                    'Excellent' => 'success',
                                                    'Très bon' => 'info',
                                                    'Bon' => 'primary',
                                                    'À améliorer' => 'warning',
                                                    'Inactif' => 'danger'
                                                ];
                                                $scoreColor = $scoreColors[$score] ?? 'secondary';
                                                
                                                // Couleur pour l'écart
                                                $ecartColor = $performance['avg_ecart'] > 0 ? 'success' : 
                                                            ($performance['avg_ecart'] < 0 ? 'danger' : 'secondary');
                                            ?>
                                            <tr class="<?php echo e($isStationA ? 'bg-light-warning' : ($isSelected ? 'bg-light-primary' : '')); ?>">
                                                <td>
                                                    <?php echo e($index + 1); ?>

                                                    <?php if($isStationA): ?>
                                                        <i class="fas fa-star text-warning" title="Station Pilote"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo e($station->nom); ?></strong><br>
                                                    <small class="text-muted"><?php echo e($station->code); ?></small>
                                                </td>
                                                <td><?php echo e($station->manager->name ?? 'Non assigné'); ?></td>
                                                <td class="font-weight-bold text-primary">
                                                    <?php echo e(number_format($performance['total_ventes'], 0, ',', ' ')); ?>

                                                </td>
                                                <td>
                                                    <?php echo e(number_format($performance['total_litres'], 0, ',', ' ')); ?>

                                                </td>
                                                <td><?php echo e($performance['shifts_count']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo e($ecartColor); ?>">
                                                        <?php echo e(number_format($performance['avg_ecart'], 0, ',', ' ')); ?> F
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar bg-info" 
                                                             style="width: <?php echo e(min($performance['pourcentage_total'], 100)); ?>%">
                                                        </div>
                                                    </div>
                                                    <small><?php echo e(number_format($performance['pourcentage_total'], 1, ',', ' ')); ?>%</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo e($scoreColor); ?>">
                                                        <?php echo e(ucfirst($score)); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?php echo e(route('chief.rapports.stations', ['station_id' => $station->id])); ?>" 
                                                           class="btn btn-info" title="Voir rapport">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?php echo e(route('chief.stations.show', $station->id)); ?>" 
                                                           class="btn btn-secondary" title="Fiche station">
                                                            <i class="fas fa-file-alt"></i>
                                                        </a>
                                                        <a href="<?php echo e(route('chief.dashboard', ['station_id' => $station->id])); ?>" 
                                                           class="btn btn-primary" title="Dashboard">
                                                            <i class="fas fa-tachometer-alt"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>


                    <!-- SECTION 5: ÉVOLUTION DES VENTES EN TEMPS RÉEL -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-line mr-2"></i> Évolution des ventes
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" id="refreshChart">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div style="position: relative; height: 300px;">
                                        <canvas id="salesEvolutionChart"></canvas>
                                    </div>
                                    <div class="text-center mt-3">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" data-period="7">7 jours</button>
                                            <button type="button" class="btn btn-outline-primary active" data-period="30">30 jours</button>
                                            <button type="button" class="btn btn-outline-primary" data-period="90">90 jours</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-muted text-center">
                                    <small>Dernière mise à jour: <span id="lastUpdate">Chargement...</span></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 6: HISTORIQUE DES SHIFTS -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5><i class="fas fa-history mr-2"></i> Historique des shifts récents</h5>
                            
                            <?php if(isset($recentShifts) && $recentShifts->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Station</th>
                                                <th>Shift</th>
                                                <th>Responsable</th>
                                                <th>Ventes (FCFA)</th>
                                                <th>Volume (L)</th>
                                                <th>Écart (FCFA)</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $recentShifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $statusBadge = [
                                                        'en_attente' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'En attente'],
                                                        'valide' => ['class' => 'success', 'icon' => 'check', 'text' => 'Validé'],
                                                        'rejete' => ['class' => 'danger', 'icon' => 'times', 'text' => 'Rejeté'],
                                                    ][$shift->statut] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => 'Inconnu'];
                                                    
                                                    $isStationA = $shift->station && $shift->station->code == 'A';
                                                    $isSelected = isset($stationId) && $stationId && $shift->station_id == $stationId;
                                                ?>
                                                <tr class="<?php echo e($isStationA ? 'bg-light-warning' : ($isSelected ? 'bg-light-primary' : '')); ?>">
                                                    <td><?php echo e($shift->date_shift->format('d/m/Y')); ?></td>
                                                    <td>
                                                        <?php if($isStationA): ?>
                                                            <i class="fas fa-star text-warning mr-1"></i>
                                                        <?php endif; ?>
                                                        <?php echo e($shift->station->nom ?? 'N/A'); ?>

                                                    </td>
                                                    <td><?php echo e($shift->shift); ?></td>
                                                    <td><?php echo e($shift->responsable); ?></td>
                                                    <td class="font-weight-bold text-primary">
                                                        <?php echo e(number_format($shift->total_ventes, 0, ',', ' ')); ?>

                                                    </td>
                                                    <td><?php echo e(number_format($shift->total_litres, 0, ',', ' ')); ?> L</td>
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
                                
                                <?php if(isset($recentShifts) && method_exists($recentShifts, 'links') && $recentShifts->hasPages()): ?>
                                    <div class="mt-3">
                                        <?php echo e($recentShifts->links()); ?>

                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Aucun shift trouvé pour la période sélectionnée
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- SECTION 7: RAPPORT DÉTAILLÉ PAR STATION -->
                    <?php if(isset($stationId) && isset($stations) && $stations->count() > 0): ?>
                        <?php
                            $selectedStation = $stations->first();
                            $stationPerformance = collect($stationPerformances ?? [])
                                ->firstWhere('station.id', $selectedStation->id);
                            $stationStock = $stockData[$selectedStation->id] ?? [];
                        ?>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-bar mr-2"></i>
                                            Rapport détaillé - <?php echo e($selectedStation->nom); ?>

                                            <span class="badge badge-light ml-2"><?php echo e($selectedStation->code); ?></span>
                                            <?php if($selectedStation->code == 'A'): ?>
                                                <span class="badge badge-warning ml-1">Pilote</span>
                                            <?php endif; ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Informations station -->
                                            <div class="col-md-4">
                                                <h6>Informations station</h6>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th width="40%">Manager:</th>
                                                        <td><?php echo e($selectedStation->manager->name ?? 'Non assigné'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Ville:</th>
                                                        <td><?php echo e($selectedStation->ville ?? 'Non spécifiée'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Adresse:</th>
                                                        <td><?php echo e($selectedStation->adresse ?? 'Non spécifiée'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Statut:</th>
                                                        <td>
                                                            <span class="badge badge-<?php echo e($selectedStation->statut == 'actif' ? 'success' : 'danger'); ?>">
                                                                <?php echo e($selectedStation->statut == 'actif' ? 'Active' : 'Inactive'); ?>

                                                            </span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            
                                            <!-- Performance station -->
                                            <?php if($stationPerformance): ?>
                                            <div class="col-md-4">
                                                <h6>Performance commerciale</h6>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th width="60%">Ventes totales:</th>
                                                        <td class="font-weight-bold text-primary">
                                                            <?php echo e(number_format($stationPerformance['total_ventes'], 0, ',', ' ')); ?> FCFA
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Volume total:</th>
                                                        <td><?php echo e(number_format($stationPerformance['total_litres'], 0, ',', ' ')); ?> L</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Nombre de shifts:</th>
                                                        <td><?php echo e($stationPerformance['shifts_count']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Écart moyen:</th>
                                                        <td>
                                                            <span class="badge badge-<?php echo e($stationPerformance['avg_ecart'] > 0 ? 'success' : ($stationPerformance['avg_ecart'] < 0 ? 'danger' : 'secondary')); ?>">
                                                                <?php echo e(number_format($stationPerformance['avg_ecart'], 0, ',', ' ')); ?> FCFA
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Part de marché:</th>
                                                        <td><?php echo e(number_format($stationPerformance['pourcentage_total'], 1, ',', ' ')); ?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Évaluation:</th>
                                                        <td>
                                                            <?php
                                                                $scoreColors = [
                                                                    'Excellent' => 'success',
                                                                    'Très bon' => 'info',
                                                                    'Bon' => 'primary',
                                                                    'À améliorer' => 'warning',
                                                                    'Inactif' => 'danger'
                                                                ];
                                                                $score = $stationPerformance['performance'] ?? 'À améliorer';
                                                                $scoreColor = $scoreColors[$score] ?? 'secondary';
                                                            ?>
                                                            <span class="badge badge-<?php echo e($scoreColor); ?>">
                                                                <?php echo e(ucfirst($score)); ?>

                                                            </span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Données de stock -->
                                            <div class="col-md-4">
                                                <h6>Données de stock</h6>
                                                <?php
                                                    $fuelTypes = ['super', 'gasoil'];
                                                ?>
                                                <?php $__currentLoopData = $fuelTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuelType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if(isset($stationStock[$fuelType])): ?>
                                                        <?php
                                                            $stock = $stationStock[$fuelType];
                                                            $fuelName = $fuelType == 'super' ? 'Super' : 'Gasoil';
                                                            $color = $fuelType == 'super' ? 'danger' : 'warning';
                                                        ?>
                                                        <div class="card mb-2">
                                                            <div class="card-header py-1 bg-<?php echo e($color); ?> text-white">
                                                                <small class="font-weight-bold"><?php echo e($fuelName); ?></small>
                                                            </div>
                                                            <div class="card-body py-2">
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <small class="text-muted">Stock actuel:</small><br>
                                                                        <span class="font-weight-bold"><?php echo e(number_format($stock['current_stock'] ?? 0, 0, ',', ' ')); ?> L</span>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <small class="text-muted">Reçu/Vendu:</small><br>
                                                                        <span class="font-weight-bold"><?php echo e(number_format($stock['received'] ?? 0, 0, ',', ' ')); ?>/<?php echo e(number_format($stock['sold'] ?? 0, 0, ',', ' ')); ?> L</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="text-center">
                                            <a href="<?php echo e(route('chief.dashboard', ['station_id' => $selectedStation->id])); ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard Station
                                            </a>
                                            <a href="<?php echo e(route('chief.stations.show', $selectedStation->id)); ?>" 
                                               class="btn btn-info btn-sm ml-2">
                                                <i class="fas fa-eye mr-1"></i> Fiche complète
                                            </a>
                                            <a href="<?php echo e(route('chief.validations', ['station' => $selectedStation->id])); ?>" 
                                               class="btn btn-warning btn-sm ml-2">
                                                <i class="fas fa-clipboard-check mr-1"></i> Validations
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- SECTION 8: DONNÉES DE STOCK PAR STATION -->
               <?php if(isset($stationId) && isset($stations) && $stations->count() > 0): ?>
    <?php
        $selectedStation = $stations->first();
        $stationStock = $stockData[$selectedStation->id] ?? [];
        
        // Utiliser les données de jaugeage déjà chargées
        $tankLevels = $tankLevelsData[$selectedStation->id] ?? collect();
    ?>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-database mr-2"></i>
                        Données de Stock avec Jaugeage - <?php echo e($selectedStation->nom); ?>

                        <span class="badge badge-light ml-2">Stock Théorique vs Physique (Jaugeage)</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Carburant</th>
                                    <th>Stock Théorique (L)</th>
                                    <th>Stock Physique (L)</th>
                                    <th>Écart (L)</th>
                                    <th>Écart (‰)</th>
                                    <th>Date Jaugeage</th>
                                    <th>Conformité</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $fuelTypes = ['super', 'gasoil'];
                                    $hasPhysicalStock = false;
                                    $acceptableThreshold = 5; // 5‰
                                    $warningThreshold = 10;   // 10‰
                                ?>
                                
                                <?php $__currentLoopData = $fuelTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuelType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(isset($stationStock[$fuelType])): ?>
                                        <?php
                                            $stock = $stationStock[$fuelType];
                                            $fuelName = $fuelType == 'super' ? 'Super' : 'Gasoil';
                                            $fuelColor = $fuelType == 'super' ? 'danger' : 'warning';
                                            
                                            // Récupérer le dernier jaugeage depuis $tankLevels
                                            $lastTankLevel = isset($tankLevels[$fuelType]) 
                                                ? $tankLevels[$fuelType]->first()
                                                : null;
                                            
                                            if ($fuelType === 'gasoil' && !$lastTankLevel) {
                                                // Essayer avec 'gazole' (variante)
                                                $lastTankLevel = isset($tankLevels['gazole']) 
                                                    ? $tankLevels['gazole']->first()
                                                    : null;
                                            }
                                            
                                            // Données théoriques
                                            $theoretical = $stock['current_stock'] ?? 0;
                                            
                                            // Données physiques du jaugeage
                                            $physical = $lastTankLevel ? $lastTankLevel->volume_liters ?? $lastTankLevel->physical_stock : null;
                                            $measurementDate = $lastTankLevel ? $lastTankLevel->measurement_date : null;
                                            
                                            // Calcul des écarts si jaugeage disponible
                                            $differenceLiters = null;
                                            $differencePerMille = null;
                                            $isAcceptable = null;
                                            $differenceClass = 'secondary';
                                            
                                            if ($lastTankLevel && $physical > 0 && $theoretical > 0) {
                                                // Utiliser les données du jaugeage
                                                $differenceLiters = $lastTankLevel->difference ?? ($physical - $theoretical);
                                                $differencePerMille = $lastTankLevel->difference_percentage ?? 0;
                                                
                                                // Déterminer la couleur selon l'écart
                                                $absPerMille = abs($differencePerMille);
                                                if ($absPerMille <= $acceptableThreshold) {
                                                    $differenceClass = 'success';
                                                } elseif ($absPerMille <= $warningThreshold) {
                                                    $differenceClass = 'warning';
                                                } else {
                                                    $differenceClass = 'danger';
                                                }
                                                
                                                $isAcceptable = $lastTankLevel->is_acceptable ?? ($absPerMille <= $acceptableThreshold);
                                                $hasPhysicalStock = true;
                                            }
                                            
                                            // Format d'affichage
                                            $diffPerMilleFormatted = $differencePerMille !== null 
                                                ? ($differencePerMille >= 0 
                                                    ? '+' . number_format($differencePerMille, 1, ',', ' ') 
                                                    : number_format($differencePerMille, 1, ',', ' '))
                                                : null;
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-<?php echo e($fuelColor); ?>"><?php echo e($fuelName); ?></span>
                                                <?php if($lastTankLevel && $lastTankLevel->tank): ?>
                                                    <br><small class="text-muted">Cuve <?php echo e($lastTankLevel->tank->number); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-weight-bold text-primary">
                                                <?php echo e(number_format($theoretical, 0, ',', ' ')); ?> L
                                            </td>
                                            <td class="font-weight-bold <?php echo e($physical ? 'text-info' : 'text-muted'); ?>">
                                                <?php if($physical): ?>
                                                    <?php echo e(number_format($physical, 0, ',', ' ')); ?> L
                                                <?php else: ?>
                                                    <small class="text-muted">Aucun jaugeage</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($differenceLiters !== null): ?>
                                                    <span class="badge badge-<?php echo e($differenceClass); ?>">
                                                        <?php echo e($differenceLiters >= 0 ? '+' : ''); ?><?php echo e(number_format($differenceLiters, 0, ',', ' ')); ?> L
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($differencePerMille !== null): ?>
                                                    <span class="badge badge-<?php echo e($differenceClass); ?>">
                                                        <?php echo e($diffPerMilleFormatted); ?><span style="font-size: 0.8em;">‰</span>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($measurementDate): ?>
                                                    <?php echo e(\Carbon\Carbon::parse($measurementDate)->format('d/m/Y H:i')); ?>

                                                <?php else: ?>
                                                    <small class="text-muted">-</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($isAcceptable !== null): ?>
                                                    <?php if($isAcceptable): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check mr-1"></i> Conforme
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i> Non conforme
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                           
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if(!$hasPhysicalStock): ?>
                                    <tr class="bg-light-warning">
                                        <td colspan="8" class="text-center">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Aucun jaugeage disponible pour la période sélectionnée.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <!-- Légende -->
                                <tr class="bg-light">
                                    <td colspan="8">
                                        <small>
                                            <i class="fas fa-info-circle text-info mr-1"></i>
                                            <strong>Légende tolérance:</strong> 
                                            <span class="badge badge-success mr-2">Écart ≤ 5‰ (Conforme)</span>
                                            <span class="badge badge-warning mr-2">5‰ < Écart ≤ 10‰ (Attention)</span>
                                            <span class="badge badge-danger mr-2">Écart > 10‰ (Non conforme)</span>
                                            <span class="badge badge-secondary">Pas de jaugeage</span>
                                        </small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                                        
                                        <!-- Résumé statistique en pour mille -->
                                        <?php
                                            $totalTheoretical = 0;
                                            $totalPhysical = 0;
                                            $totalDifferenceLiters = 0;
                                            $weightedPerMille = 0;
                                            $countWithPhysical = 0;
                                            
                                            foreach($fuelTypes as $fuelType) {
                                                if(isset($stationStock[$fuelType])) {
                                                    $stock = $stationStock[$fuelType];
                                                    $theoretical = $stock['current_stock'] ?? 0;
                                                    $physical = $stock['physical_stock'] ?? 0;
                                                    
                                                    if($physical > 0) {
                                                        $totalTheoretical += $theoretical;
                                                        $totalPhysical += $physical;
                                                        $countWithPhysical++;
                                                    }
                                                }
                                            }
                                            
                                            if($totalTheoretical > 0 && $countWithPhysical > 0) {
                                                $totalDifferenceLiters = $totalTheoretical - $totalPhysical;
                                                $totalPerMille = ($totalDifferenceLiters / $totalTheoretical) * 1000;
                                            }
                                        ?>
                                        
                                                                     
                                        <!-- Graphiques de stock -->
                                        <?php if(count($stationStock) > 0): ?>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="card-title mb-0">Comparaison Stock Théorique vs Physique</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div style="position: relative; height: 250px;">
                                                            <canvas id="stockComparisonChart"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="card-title mb-0">Réception vs Vente (Litres)</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div style="position: relative; height: 250px;">
                                                            <canvas id="receptionVsSaleChart"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    Stock théorique calculé au: <?php echo e(now()->format('d/m/Y H:i')); ?>

                                                </small>
                                            </div>
                                            <div class="col-md-6 text-right">
                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                            
                            <?php if(isset($stationId) && $stationId): ?>
                                <a href="<?php echo e(route('chief.rapports.stations')); ?>" 
                                   class="btn btn-secondary ml-2">
                                    <i class="fas fa-list mr-1"></i> Voir toutes les stations
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Période : <?php echo e(\Carbon\Carbon::parse($startDate ?? now()->startOfMonth())->format('d/m/Y')); ?> 
                                au <?php echo e(\Carbon\Carbon::parse($endDate ?? now())->format('d/m/Y')); ?>

                                <?php if(isset($stations) && $stations->count() == 1): ?>
                                    | Station : <?php echo e($stations->first()->nom); ?>

                                <?php else: ?>
                                    | Nombre de stations : <?php echo e(isset($stations) ? $stations->count() : 0); ?>

                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                Généré le <?php echo e(now()->format('d/m/Y H:i')); ?> |
                                <i class="fas fa-user-shield mr-1"></i> Interface Chief
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .info-box {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 15px;
        transition: transform 0.2s;
    }
    
    .info-box:hover {
        transform: translateY(-2px);
    }
    
    .info-box-icon {
        font-size: 28px;
    }
    
    .info-box-content {
        padding: 15px;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(45deg, #007bff, #6610f2) !important;
        color: white;
    }
    
    .bg-gradient-info {
        background: linear-gradient(45deg, #17a2b8, #007bff) !important;
        color: white;
    }
    
    .bg-gradient-success {
        background: linear-gradient(45deg, #28a745, #20c997) !important;
        color: white;
    }
    
    .bg-gradient-warning {
        background: linear-gradient(45deg, #ffc107, #ff9800) !important;
        color: white;
    }
    
    .bg-gradient-danger {
        background: linear-gradient(45deg, #dc3545, #c82333) !important;
        color: white;
    }
    
    .card-title .badge {
        font-size: 0.6em;
        vertical-align: middle;
    }
    
    .bg-light-warning {
        background-color: #fff3cd !important;
    }
    
    .bg-light-primary {
        background-color: #e3f2fd !important;
    }
    
    .progress-sm {
        height: 0.5rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    
    let salesChart = null;
    let chartDataPeriod = 30;
    
    function loadSalesEvolution() {
        var stationId = $('select[name="station_id"]').val() || '<?php echo e($stationId ?? ""); ?>';
        
        $.ajax({
            url: '/chief/sales-evolution',
            type: 'GET',
            data: {
                period: chartDataPeriod,
                station_id: stationId
            },
            success: function(response) {
                console.log('Données reçues:', response);
                if (response.success && response.data) {
                    updateChart(response.data);
                    updateStats(response.stats);
                    $('#lastUpdate').text(response.timestamp || 'Dernière mise à jour: ' + new Date().toLocaleTimeString());
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors du chargement des données:', error);
                $('#lastUpdate').text('Erreur de chargement');
            }
        });
    }

    function updateChart(data) {
        const ctx = document.getElementById('salesEvolutionChart').getContext('2d');
        
        if (salesChart) {
            salesChart.destroy();
        }
        
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Ventes (FCFA)',
                    data: data.sales,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + 
                                       context.parsed.y.toLocaleString('fr-FR') + ' FCFA';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
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
                }
            }
        });
    }

    function updateStats(stats) {
        if (stats) {
            let statsHtml = `
                <div class="row mt-3" id="chartStats">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Ventes</span>
                                                <span class="info-box-number">
                                                    ${stats.total_sales ? (stats.total_sales / 1000000).toFixed(2) + 'M' : '0'} FCFA
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text">Moyenne/jour</span>
                                                <span class="info-box-number">
                                                    ${stats.average_sales ? Math.round(stats.average_sales).toLocaleString('fr-FR') : '0'} FCFA
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text">Pic de vente</span>
                                                <span class="info-box-number">
                                                    ${stats.max_sales ? Math.round(stats.max_sales).toLocaleString('fr-FR') : '0'} FCFA
                                                </span>
                                                <small class="text-muted">Le ${stats.peak_date || 'N/A'}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Shifts</span>
                                                <span class="info-box-number">${stats.total_shifts || 0}</span>
                                                <small class="text-muted">${(stats.total_litres || 0).toLocaleString('fr-FR')} litres</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            if (!$('#chartStats').length) {
                $('#salesEvolutionChart').closest('.card').after(statsHtml);
            } else {
                $('#chartStats').replaceWith(statsHtml);
            }
        }
    }

    // Graphique de distribution des carburants
    <?php if(isset($fuelDistribution)): ?>
    var superVolume = <?php echo e($fuelDetails['super']['volume'] ?? 0); ?>;
    var gasoilVolume = <?php echo e($fuelDetails['gasoil']['volume'] ?? 0); ?>;
    
    if (superVolume > 0 || gasoilVolume > 0) {
        const fuelCtx = document.getElementById('fuelDistributionChart').getContext('2d');
        const fuelChart = new Chart(fuelCtx, {
            type: 'doughnut',
            data: {
                labels: ['Super', 'Gasoil'],
                datasets: [{
                    data: [superVolume, gasoilVolume],
                    backgroundColor: [
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderColor: [
                        'rgba(220, 53, 69, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 1
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
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = superVolume + gasoilVolume;
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value.toLocaleString('fr-FR')} L (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    // Graphiques de stock
    <?php if(isset($stationId) && isset($stations) && $stations->count() > 0 && isset($stockData)): ?>
    var stationStock = <?php echo json_encode($stockData[$stations->first()->id] ?? [], 15, 512) ?>;
    
    // Graphique de comparaison stock théorique vs physique
    if (stationStock && Object.keys(stationStock).length > 0) {
        var comparisonCtx = document.getElementById('stockComparisonChart').getContext('2d');
        var comparisonChart = new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: ['Super', 'Gasoil'],
                datasets: [
                    {
                        label: 'Stock Théorique (L)',
                        data: [
                            stationStock.super?.current_stock || 0,
                            stationStock.gasoil?.current_stock || 0
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Stock Physique (L)',
                        data: [
                            stationStock.super?.physical_stock || 0,
                            stationStock.gasoil?.physical_stock || 0
                        ],
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Litres (L)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('fr-FR') + ' L';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + 
                                       context.parsed.y.toLocaleString('fr-FR') + ' L';
                            }
                        }
                    }
                }
            }
        });
        
        // Graphique réception vs vente
        var receptionVsSaleCtx = document.getElementById('receptionVsSaleChart').getContext('2d');
        var receptionVsSaleChart = new Chart(receptionVsSaleCtx, {
            type: 'bar',
            data: {
                labels: ['Super', 'Gasoil'],
                datasets: [
                    {
                        label: 'Reçu (L)',
                        data: [
                            stationStock.super?.received || 0,
                            stationStock.gasoil?.received || 0
                        ],
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Vendu (L)',
                        data: [
                            stationStock.super?.sold || 0,
                            stationStock.gasoil?.sold || 0
                        ],
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Litres (L)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('fr-FR') + ' L';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + 
                                       context.parsed.y.toLocaleString('fr-FR') + ' L';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    // Boutons de période
    $('[data-period]').on('click', function() {
        $('[data-period]').removeClass('active');
        $(this).addClass('active');
        chartDataPeriod = $(this).data('period');
        loadSalesEvolution();
    });

    // Rafraîchir le graphique
    $('#refreshChart').on('click', function() {
        $(this).addClass('fa-spin');
        loadSalesEvolution();
        setTimeout(() => {
            $('#refreshChart').removeClass('fa-spin');
        }, 1000);
    });

    // Charger initialement
    loadSalesEvolution();

    // Auto-refresh toutes les 2 minutes
    setInterval(loadSalesEvolution, 120000);
    
    // Recherche dans les tableaux
    $('input[type="search"]').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        var tableId = $(this).data('table');
        $('#' + tableId + ' tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Tri des colonnes
    $('th[data-sort]').on('click', function() {
        var table = $(this).closest('table');
        var column = $(this).index();
        var rows = table.find('tbody tr').toArray();
        
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(column).text();
            var bVal = $(b).find('td').eq(column).text();
            
            var aNum = parseFloat(aVal.replace(/[^0-9.-]+/g, ""));
            var bNum = parseFloat(bVal.replace(/[^0-9.-]+/g, ""));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return aNum - bNum;
            }
            return aVal.localeCompare(bVal);
        });
        
        table.find('tbody').empty().append(rows);
    });
    
    // Mise en évidence des lignes
    $('table tbody tr').hover(
        function() {
            $(this).addClass('table-active');
        },
        function() {
            $(this).removeClass('table-active');
        }
    );
    
    // Export PDF
    $('.export-pdf').on('click', function() {
        var period = "<?php echo e($startDate ?? ''); ?>_<?php echo e($endDate ?? ''); ?>";
        var station = "<?php echo e($stationId ?? 'all'); ?>";
        var filename = 'rapport_stations_' + period + '_' + station + '.pdf';
        
        alert('Export PDF: ' + filename);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.chief', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/chief/stations/rapports.blade.php ENDPATH**/ ?>
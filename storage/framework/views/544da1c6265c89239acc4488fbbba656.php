

<?php $__env->startSection('title', 'Liste des cuves'); ?>
<?php $__env->startSection('page-title', 'Gestion des cuves'); ?>

<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('manager.stocks.dashboard')); ?>">Tableau de bord</a></li>
<li class="breadcrumb-item active">Cuves</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #333, #555); color: white;">
                <h3 class="card-title">
                    <i class="fas fa-oil-can mr-2"></i>Liste des cuves
                </h3>
                <div class="card-tools">
                    <a href="<?php echo e(route('manager.tanks.create')); ?>" class="btn btn-sm" style="background-color: #FF7F00; color: white;">
                        <i class="fas fa-plus mr-1"></i> Nouvelle cuve
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i> <?php echo e(session('success')); ?>

                </div>
                <?php endif; ?>
                
                <?php if($tanks->isEmpty()): ?>
                <div class="alert alert-info">
                    <i class="icon fas fa-info-circle"></i>
                    Aucune cuve créée. 
                    <a href="<?php echo e(route('manager.tanks.create')); ?>" class="alert-link">
                        Créez votre première cuve
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th>Numéro</th>
                                <th>Carburant</th>
                                <th>Capacité</th>
                                <th>Stock actuel</th>
                                <th>Remplissage</th>
                                <th>Description</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $tanks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($tank->number); ?></strong>
                                </td>
                                <td>
                                    <?php
                                        $badgeClass = [
                                            'super' => 'badge-danger',
                                            'gasoil' => 'badge-success',
                                            'Essence Pirogue' => 'badge-primary',
                                            
                                        ][$tank->fuel_type] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?php echo e($badgeClass); ?>">
                                        <?php echo e(strtoupper($tank->fuel_type)); ?>

                                    </span>
                                </td>
                                <td><?php echo e(number_format($tank->capacity)); ?> L</td>
                                <td><?php echo e(number_format($tank->current_volume, 0)); ?> L</td>
                                <td>
                                    <?php
                                        $percentage = $tank->capacity > 0 ? ($tank->current_volume / $tank->capacity) * 100 : 0;
                                    ?>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar 
                                            <?php if($percentage > 80): ?> bg-success
                                            <?php elseif($percentage > 50): ?> bg-info
                                            <?php elseif($percentage > 20): ?> bg-warning
                                            <?php else: ?> bg-danger
                                            <?php endif; ?>" 
                                            style="width: <?php echo e($percentage); ?>%">
                                        </div>
                                    </div>
                                    <small><?php echo e(number_format($percentage, 1)); ?>%</small>
                                </td>
                                <td><?php echo e($tank->description ?? '-'); ?></td>
                               
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Total : <?php echo e($tanks->count()); ?> cuve(s)</strong>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="<?php echo e(route('manager.stocks.dashboard')); ?>" class="btn btn-default">
                            <i class="fas fa-tachometer-alt mr-1"></i> Tableau de bord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.table th {
    border-top: 2px solid #FF7F00;
}

.progress {
    height: 20px;
    margin-bottom: 5px;
}

.progress-bar {
    border-radius: 3px;
}
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/manager/tanks/index.blade.php ENDPATH**/ ?>


<?php $__env->startSection('title', 'Historique des Mouvements de Stock'); ?>
<?php $__env->startSection('page-title', 'Historique Complet des Mouvements'); ?>

<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('manager.stocks.dashboard')); ?>">Stocks</a></li>
<li class="breadcrumb-item active">Historique</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Filtres -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtres</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('manager.stocks.history')); ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label>Type de Carburant</label>
                        <select name="fuel_type" class="form-control">
                            <option value="">Tous les carburants</option>
                            <?php $__currentLoopData = $fuelTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e(request('fuel_type') == $key ? 'selected' : ''); ?>>
                                    <?php echo e($name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Date de début</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="<?php echo e(request('start_date')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Date de fin</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="<?php echo e(request('end_date')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Récapitulatif -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-truck-loading"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Réceptions</span>
                    <span class="info-box-number"><?php echo e(number_format($totals['receptions'], 2, ',', ' ')); ?> L</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ventes</span>
                    <span class="info-box-number"><?php echo e(number_format($totals['sales'], 2, ',', ' ')); ?> L</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ajustements</span>
                    <span class="info-box-number"><?php echo e(number_format($totals['adjustments'], 2, ',', ' ')); ?> L</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tableau des mouvements -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mouvements de Stock</h3>
            <div class="card-tools">
                <span class="badge badge-primary"><?php echo e($movements->total()); ?> mouvements</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Carburant</th>
                            <th>Quantité (L)</th>
                            <th>Prix Unitaire</th>
                            <th>Total</th>
                            <th>Stock Avant</th>
                            <th>Stock Après</th>
                            <th>Détails</th>
                            <th>Enregistré par</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isReception = $movement->movement_type === 'reception';
                                $isSale = $movement->movement_type === 'vente';
                                $isAdjustment = $movement->movement_type === 'ajustement';
                                
                                $typeClass = $isReception ? 'badge-success' : 
                                            ($isSale ? 'badge-danger' : 'badge-warning');
                                
                                $quantityClass = $movement->quantity > 0 ? 'text-success' : 
                                               ($movement->quantity < 0 ? 'text-danger' : 'text-muted');
                            ?>
                            <tr>
                                <td><?php echo e($movement->movement_date->format('d/m/Y H:i')); ?></td>
                                <td>
                                    <span class="badge <?php echo e($typeClass); ?>">
                                        <?php echo e(ucfirst($movement->movement_type)); ?>

                                        <?php if($movement->auto_generated): ?>
                                            <i class="fas fa-robot ml-1"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?php echo e(ucfirst($movement->fuel_type)); ?></td>
                                <td class="<?php echo e($quantityClass); ?> font-weight-bold">
                                    <?php echo e($movement->quantity > 0 ? '+' : ''); ?><?php echo e(number_format($movement->quantity, 2, ',', ' ')); ?>

                                </td>
                                <td>
                                    <?php if($movement->unit_price > 0): ?>
                                        <?php echo e(number_format($movement->unit_price, 0, ',', ' ')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($movement->total_amount != 0): ?>
                                        <?php echo e(number_format($movement->total_amount, 0, ',', ' ')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(number_format($movement->stock_before, 2, ',', ' ')); ?></td>
                                <td><?php echo e(number_format($movement->stock_after, 2, ',', ' ')); ?></td>
                                <td>
                                    <?php if($isReception): ?>
                                        <small>
                                            <i class="fas fa-truck"></i> <?php echo e($movement->supplier_name); ?><br>
                                            BL: <?php echo e($movement->invoice_number); ?><br>
                                            Cuve: <?php echo e($movement->tank_number); ?>

                                        </small>
                                    <?php elseif($isSale && $movement->shiftSaisie): ?>
                                        <small>
                                            <i class="fas fa-cash-register"></i> Shift #<?php echo e($movement->shiftSaisie->id); ?><br>
                                            <?php echo e($movement->shiftSaisie->responsable); ?>

                                        </small>
                                    <?php elseif($isAdjustment): ?>
                                        <small class="text-muted"><?php echo e(Str::limit($movement->notes, 50)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo e(optional($movement->recorder)->name); ?></small><br>
                                    <small class="text-muted"><?php echo e($movement->created_at->format('H:i')); ?></small>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <?php echo e($movements->appends(request()->query())->links()); ?>

        </div>
    </div>
    
    <!-- Actions -->
    <div class="row mt-3">
        <div class="col-md-6">
            <a href="<?php echo e(route('manager.stocks.dashboard')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo e(route('manager.stocks.balance')); ?>" class="btn btn-info">
                <i class="fas fa-chart-pie"></i> Voir le bilan détaillé
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/manager/stocks/history.blade.php ENDPATH**/ ?>


<?php $__env->startSection('title', 'Historique des Saisies'); ?>
<?php $__env->startSection('page-title', 'Historique des Shifts'); ?>

<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('manager.index_form')); ?>">Manager</a></li>
<li class="breadcrumb-item active">Historique</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header odyssee-bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-history"></i> Historique des Shifts
        </h3>
        <div class="card-tools">
            <a href="<?php echo e(route('manager.index_form')); ?>" class="btn btn-light btn-sm">
                <i class="fas fa-plus"></i> Nouvelle Saisie
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th width="60">ID</th>
                        <th>Date Shift</th>
                        <th>Shift</th>
                        <th>Station</th>
                        <th>Responsable</th>
                        <th class="text-right">Total Ventes</th>
                        <th class="text-right">Versement</th>
                        <th class="text-right">Écart Final</th>
                        <th class="text-center" width="180">Statut</th>
                        <th class="text-center" width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $saisies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saisie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-weight-bold">#<?php echo e($saisie->id); ?></td>
                        <td>
                            <i class="far fa-calendar text-muted mr-1"></i>
                            <?php echo e($saisie->date_shift->format('d/m/Y')); ?>

                        </td>
                        <td>
                            <span class="badge badge-info"><?php echo e($saisie->shift); ?></span>
                        </td>
                        <!-- COLONNE STATION -->
                        <td>
                            <?php if($saisie->station): ?>
                                <span class="badge badge-secondary"><?php echo e($saisie->station->code); ?></span>
                                <br><small><?php echo e($saisie->station->nom); ?></small>
                            <?php else: ?>
                                <span class="badge badge-light">Non assigné</span>
                            <?php endif; ?>
                        </td>
                        <!-- FIN COLONNE STATION -->
                        <td><?php echo e($saisie->responsable); ?></td>
                        <td class="text-right font-weight-bold text-primary">
                            <?php echo e(number_format($saisie->total_ventes, 0, ',', ' ')); ?> F CFA
                        </td>
                        <td class="text-right">
                            <?php echo e(number_format($saisie->versement, 0, ',', ' ')); ?> F CFA
                        </td>
                        
                        <!-- COLONNE ÉCART FINAL -->
                        <td class="text-right">
                            <?php
                                // Calculer directement l'écart selon la formule : Versement - (Ventes - Dépenses)
                                $ecart = $saisie->versement - ($saisie->total_ventes - $saisie->total_depenses);
                            ?>
                            
                            <?php if($ecart > 0): ?>
                                <span class="badge badge-success">
                                    +<?php echo e(number_format($ecart, 0, ',', ' ')); ?> F CFA
                                </span>
                                <div class="small text-success">Excédent</div>
                            <?php elseif($ecart < 0): ?>
                                <span class="badge badge-danger">
                                    <?php echo e(number_format($ecart, 0, ',', ' ')); ?> F CFA
                                </span>
                                <div class="small text-danger">Manquant</div>
                            <?php else: ?>
                                <span class="badge badge-secondary">0 F CFA</span>
                                <div class="small text-muted">Équilibré</div>
                            <?php endif; ?>
                            
                            <?php if($saisie->total_depenses > 0): ?>
                                <br><small class="text-muted">(Dépenses: <?php echo e(number_format($saisie->total_depenses, 0, ',', ' ')); ?> F CFA)</small>
                            <?php endif; ?>
                        </td>
                        
                        <!-- COLONNE STATUT -->
                        <td class="text-center">
                            <?php if(isset($saisie->statut)): ?>
                                <?php switch($saisie->statut):
                                    case ('valide'): ?>
                                        <span class="badge badge-success">Validé</span>
                                        <?php break; ?>
                                    <?php case ('rejete'): ?>
                                        <span class="badge badge-danger">Rejeté</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge badge-warning">En attente</span>
                                <?php endswitch; ?>
                                <?php if($saisie->validateur): ?>
                                    <br><small class="text-muted">Par: <?php echo e($saisie->validateur->name ?? ''); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-warning">En attente</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- COLONNE ACTIONS -->
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <!-- Voir détails -->
                                <a href="<?php echo e(route('manager.history.show', $saisie->id)); ?>" 
                                   class="btn btn-info" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <!-- Modifier -->
                                <a href="<?php echo e(route('manager.saisie.edit', $saisie->id)); ?>" 
                                   class="btn btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Télécharger PDF -->
                                <a href="<?php echo e(route('manager.saisie.pdf', $saisie->id)); ?>" 
                                   class="btn btn-success" title="Télécharger PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                
                               
                                
                                <!-- Supprimer -->
                                <button type="button" class="btn btn-danger" 
                                        onclick="confirmDelete(<?php echo e($saisie->id); ?>)" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <!-- Formulaire de suppression caché -->
                            <form id="delete-form-<?php echo e($saisie->id); ?>" 
                                  action="<?php echo e(route('manager.saisie.delete', $saisie->id)); ?>" 
                                  method="POST" style="display: none;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune saisie enregistrée</h5>
                            <a href="<?php echo e(route('manager.index_form')); ?>" class="btn odyssee-btn-primary mt-2">
                                <i class="fas fa-plus"></i> Créer votre première saisie
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if($saisies->hasPages()): ?>
    <div class="card-footer">
        <div class="float-right">
            <?php echo e($saisies->links()); ?>

        </div>
        <div class="text-muted">
            Affichage de <?php echo e($saisies->firstItem()); ?> à <?php echo e($saisies->lastItem()); ?> sur <?php echo e($saisies->total()); ?> saisies
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function confirmDelete(id) {
        if (confirm('Voulez-vous vraiment supprimer cette saisie ? Cette action est irréversible.')) {
            event.preventDefault();
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/manager/history.blade.php ENDPATH**/ ?>
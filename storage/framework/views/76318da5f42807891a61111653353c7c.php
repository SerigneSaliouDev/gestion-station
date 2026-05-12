

<?php $__env->startSection('title', 'Détails de la Saisie'); ?>
<?php $__env->startSection('page-title', 'Détails du Shift #' . $shift->id); ?>

<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('manager.index_form')); ?>">Manager</a></li>
<li class="breadcrumb-item"><a href="<?php echo e(route('manager.history')); ?>">Historique</a></li>
<li class="breadcrumb-item active">Détails</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Informations Générales -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header odyssee-bg-primary">
                <h3 class="card-title text-white">Informations du Shift</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Date :</th>
                        <td><?php echo e($shift->date_shift->format('d/m/Y')); ?></td>
                    </tr>
                    <tr>
                        <th>Shift :</th>
                        <td><span class="badge badge-info"><?php echo e($shift->shift); ?></span></td>
                    </tr>
                    <tr>
                        <th>Responsable :</th>
                        <td><?php echo e($shift->responsable); ?></td>
                    </tr>
                    <tr>
                        <th>Créé le :</th>
                        <td><?php echo e($shift->created_at->format('d/m/Y H:i')); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Résumé Financier -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header odyssee-bg-primary">
                <h3 class="card-title text-white">Résumé Financier</h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-gas-pump"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Litres</span>
                                <span class="info-box-number"><?php echo e(number_format($shift->total_litres, 2, ',', ' ')); ?> L</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Ventes</span>
                                <span class="info-box-number"><?php echo e(number_format($shift->total_ventes, 0, ',', ' ')); ?> F CFA</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning"><i class="fas fa-cash-register"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Versement</span>
                                <span class="info-box-number"><?php echo e(number_format($shift->versement, 0, ',', ' ')); ?> F CFA</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <div class="h3">
                        Écart Initial : 
                        <?php
                            $ecartFormatted = $shift->ecart_formatted;
                        ?>
                        <?php if($ecartFormatted['montant'] > 0): ?>
                            <span class="text-<?php echo e($ecartFormatted['classe']); ?>">
                                <?php echo e($ecartFormatted['signe']); ?><?php echo e(number_format($ecartFormatted['montant'], 0, ',', ' ')); ?> F CFA
                            </span>
                            <div class="small text-<?php echo e($ecartFormatted['classe']); ?>"><?php echo e($ecartFormatted['texte']); ?></div>
                        <?php elseif($ecartFormatted['montant'] < 0): ?>
                            <span class="text-<?php echo e($ecartFormatted['classe']); ?>">
                                <?php echo e($ecartFormatted['signe']); ?><?php echo e(number_format($ecartFormatted['montant'], 0, ',', ' ')); ?> F CFA
                            </span>
                            <div class="small text-<?php echo e($ecartFormatted['classe']); ?>"><?php echo e($ecartFormatted['texte']); ?></div>
                        <?php else: ?>
                            <span class="text-muted">0 F CFA</span>
                            <div class="small text-muted"><?php echo e($ecartFormatted['texte']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Détails des Pompes -->
<div class="card mt-3">
    <div class="card-header odyssee-bg-primary">
        <h3 class="card-title text-white">Détail des Pompes</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>Pompe</th>
                        <th>Carburant</th>
                        <th class="text-center">Prix Unitaire</th>
                        <th class="text-center">Index Ouverture</th>
                        <th class="text-center">Index Fermeture</th>
                        <th class="text-center">Retour (L)</th>
                        <th class="text-center">Litrage Vendu</th>
                        <th class="text-right">Montant Ventes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $shift->pompeDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="font-weight-bold"><?php echo e($detail->pompe_nom); ?></td>
                        <td><?php echo e($detail->carburant); ?></td>
                        <td class="text-center"><?php echo e(number_format($detail->prix_unitaire, 0, ',', ' ')); ?> F CFA</td>
                        <td class="text-center"><?php echo e(number_format($detail->index_ouverture, 2, ',', ' ')); ?> L</td>
                        <td class="text-center"><?php echo e(number_format($detail->index_fermeture, 2, ',', ' ')); ?> L</td>
                        <td class="text-center"><?php echo e(number_format($detail->retour_litres, 2, ',', ' ')); ?> L</td>
                        <td class="text-center font-weight-bold"><?php echo e(number_format($detail->litrage_vendu, 2, ',', ' ')); ?> L</td>
                        <td class="text-right font-weight-bold text-primary">
                            <?php echo e(number_format($detail->montant_ventes, 0, ',', ' ')); ?> F CFA
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <th colspan="6" class="text-right">TOTAUX :</th>
                        <th class="text-center font-weight-bold">
                            <?php echo e(number_format($shift->pompeDetails->sum('litrage_vendu'), 2, ',', ' ')); ?> L
                        </th>
                        <th class="text-right font-weight-bold">
                            <?php echo e(number_format($shift->pompeDetails->sum('montant_ventes'), 0, ',', ' ')); ?> F CFA
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Dépenses -->
<?php if($shift->depenses->count() > 0): ?>
<div class="card mt-3">
    <div class="card-header odyssee-bg-primary">
        <h3 class="card-title text-white">Dépenses du Shift</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>Type de Dépense</th>
                        <th class="text-right">Montant</th>
                        <th>Description</th>
                        <th>Justificatif</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $shift->depenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $depense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <?php switch($depense->type_depense):
                                case ('carburant_vehicule'): ?>
                                    <i class="fas fa-car text-primary mr-1"></i> Carburant Véhicule
                                    <?php break; ?>
                                <?php case ('nourriture'): ?>
                                    <i class="fas fa-utensils text-warning mr-1"></i> Nourriture
                                    <?php break; ?>
                                <?php case ('maintenance'): ?>
                                    <i class="fas fa-tools text-info mr-1"></i> Maintenance
                                    <?php break; ?>
                                <?php case ('achat_divers'): ?>
                                    <i class="fas fa-shopping-cart text-success mr-1"></i> Achat Divers
                                    <?php break; ?>
                                <?php case ('frais_transport'): ?>
                                    <i class="fas fa-bus text-secondary mr-1"></i> Frais de Transport
                                    <?php break; ?>
                                <?php default: ?>
                                    <i class="fas fa-receipt mr-1"></i> <?php echo e(ucfirst(str_replace('_', ' ', $depense->type_depense))); ?>

                            <?php endswitch; ?>
                        </td>
                        <td class="text-right font-weight-bold text-danger">
                            - <?php echo e(number_format($depense->montant, 0, ',', ' ')); ?> F CFA
                        </td>
                        <td><?php echo e($depense->description ?? 'Aucune description'); ?></td>
                        <td>
                            <?php if($depense->justificatif): ?>
                                <a href="<?php echo e(route('manager.saisie.download.justificatif', $depense->id)); ?>" 
                                   class="btn btn-sm btn-info" title="Télécharger">
                                    <i class="fas fa-download"></i>
                                </a>
                            <?php else: ?>
                                <span class="badge badge-secondary">Aucun fichier</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <th colspan="4" class="text-right">Total Dépenses:</th>
                        <th class="text-right font-weight-bold text-danger">
                            - <?php echo e(number_format($shift->total_depenses, 0, ',', ' ')); ?> F CFA
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Récapitulatif Financier Complet -->
<div class="card mt-3">
    <div class="card-header odyssee-bg-primary">
        <h3 class="card-title text-white">Récapitulatif Financier Complet</h3>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="info-box bg-light">
                    <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Ventes</span>
                        <span class="info-box-number"><?php echo e(number_format($shift->total_ventes, 0, ',', ' ')); ?> F CFA</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-light">
                    <span class="info-box-icon bg-warning"><i class="fas fa-cash-register"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Versement</span>
                        <span class="info-box-number"><?php echo e(number_format($shift->versement, 0, ',', ' ')); ?> F CFA</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-light">
                    <span class="info-box-icon bg-danger"><i class="fas fa-receipt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Dépenses</span>
                        <span class="info-box-number">- <?php echo e(number_format($shift->total_depenses, 0, ',', ' ')); ?> F CFA</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-light">
                    <span class="info-box-icon bg-info"><i class="fas fa-balance-scale"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Écart Final</span>
                        <span class="info-box-number">
                            <?php
                                $ecartFinalFormatted = $shift->ecart_final_formatted;
                            ?>
                            <?php echo e($ecartFinalFormatted['signe']); ?><?php echo e(number_format($ecartFinalFormatted['montant'], 0, ',', ' ')); ?> F CFA
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <h4>
                Calcul Final: 
                <span class="text-success"><?php echo e(number_format($shift->total_ventes, 0, ',', ' ')); ?></span> 
                - 
                <span class="text-warning"><?php echo e(number_format($shift->versement, 0, ',', ' ')); ?></span>
                <?php if($shift->total_depenses > 0): ?>
                    - 
                    <span class="text-danger"><?php echo e(number_format($shift->total_depenses, 0, ',', ' ')); ?></span>
                <?php endif; ?>
                = 
                <?php
                    $ecartFinalFormatted = $shift->ecart_final_formatted;
                ?>
                <span class="text-<?php echo e($ecartFinalFormatted['classe']); ?>">
                    <?php echo e($ecartFinalFormatted['signe']); ?><?php echo e(number_format($ecartFinalFormatted['montant'], 0, ',', ' ')); ?> F CFA
                </span>
                <div class="small text-<?php echo e($ecartFinalFormatted['classe']); ?>">(<?php echo e($ecartFinalFormatted['texte']); ?>)</div>
            </h4>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="card mt-3">
    <div class="card-body text-center">
        <div class="btn-group">
            <a href="<?php echo e(route('manager.history')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à l'historique
            </a>
            
            <a href="<?php echo e(route('manager.saisie.edit', $shift->id)); ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Modifier
            </a>
            
            <a href="<?php echo e(route('manager.saisie.pdf', $shift->id)); ?>" class="btn btn-info">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            
            
            <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo e($shift->id); ?>)">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </div>
    </div>
</div>

<form id="delete-form-<?php echo e($shift->id); ?>" 
      action="<?php echo e(route('manager.saisie.delete', $shift->id)); ?>" 
      method="POST" style="display: none;">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function confirmDelete(id) {
        if (confirm('Voulez-vous vraiment supprimer cette saisie ?')) {
            event.preventDefault();
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/manager/history-show.blade.php ENDPATH**/ ?>
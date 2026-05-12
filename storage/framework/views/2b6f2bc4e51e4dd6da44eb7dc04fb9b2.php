

<?php $__env->startSection('title', 'Détails de la Saisie'); ?>
<?php $__env->startSection('page-icon', 'fa-eye'); ?>
<?php $__env->startSection('page-title', 'Détails de la Saisie'); ?>
<?php $__env->startSection('page-subtitle', 'Examen avant validation'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('chief.dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('chief.validations')); ?>">Validations</a></li>
    <li class="breadcrumb-item active">Détails #<?php echo e($saisie->id ?? 'N/A'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            
            <?php if(session('warning')): ?>
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle"></i> <?php echo e(session('warning')); ?>

                </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-circle"></i> <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
            
            <!-- Carte principale -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-check mr-2"></i> 
                        Saisie #<?php echo e($saisie->id); ?> - 
                        <?php echo e($saisie->date_shift->format('d/m/Y')); ?> 
                        (<?php echo e($saisie->shift); ?>)
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-<?php echo e($saisie->statut === 'valide' ? 'success' : ($saisie->statut === 'rejete' ? 'danger' : 'warning')); ?>">
                            <?php echo e($saisie->statut === 'valide' ? 'Validé' : ($saisie->statut === 'rejete' ? 'Rejeté' : 'En attente')); ?>

                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Informations Générales</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td width="40%"><strong>Station:</strong></td>
                                            <td>
                                                <?php echo e($saisie->station->nom ?? 'N/A'); ?>

                                                <small class="text-muted">(<?php echo e($saisie->station->code ?? 'N/A'); ?>)</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date du shift:</strong></td>
                                            <td><?php echo e($saisie->date_shift->format('d/m/Y')); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Shift:</strong></td>
                                            <td><?php echo e($saisie->shift); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pompiste:</strong></td>
                                            <td><?php echo e($saisie->responsable); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Créé par:</strong></td>
                                            <td><?php echo e($saisie->user->name ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date création:</strong></td>
                                            <td><?php echo e($saisie->created_at->format('d/m/Y H:i')); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i> Totaux</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td width="60%"><strong>Total Ventes:</strong></td>
                                            <td class="text-right font-weight-bold text-primary">
                                                <?php echo e(number_format($saisie->total_ventes, 0, ',', ' ')); ?> FCFA
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Litres:</strong></td>
                                            <td class="text-right"><?php echo e(number_format($saisie->total_litres, 2, ',', ' ')); ?> L</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Versement:</strong></td>
                                            <td class="text-right"><?php echo e(number_format($saisie->versement, 0, ',', ' ')); ?> FCFA</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Dépenses:</strong></td>
                                            <td class="text-right text-danger">
                                                <?php echo e(number_format($saisie->total_depenses, 0, ',', ' ')); ?> FCFA
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Écart initial:</strong></td>
                                            <td class="text-right">
                                                <span class="badge badge-<?php echo e($saisie->ecart_formatted['classe'] ?? 'secondary'); ?>">
                                                    <?php echo e($saisie->ecart_formatted['signe'] ?? ''); ?>

                                                    <?php echo e(number_format($saisie->ecart_formatted['montant'] ?? 0, 0, ',', ' ')); ?> FCFA
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Écart final:</strong></td>
                                            <td class="text-right">
                                                <span class="badge badge-<?php echo e($saisie->ecart_final_formatted['classe'] ?? 'secondary'); ?>">
                                                    <?php echo e($saisie->ecart_final_formatted['signe'] ?? ''); ?>

                                                    <?php echo e(number_format($saisie->ecart_final_formatted['montant'] ?? 0, 0, ',', ' ')); ?> FCFA
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    
                    <!-- Détails des dépenses -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-danger">
                                    <h3 class="card-title">
                                        <i class="fas fa-receipt mr-2"></i> Détail des Dépenses
                                    </h3>
                                    <div class="card-tools">
                                        <span class="badge badge-light">
                                            <?php echo e($saisie->depenses->count() ?? 0); ?> dépense(s)
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <?php if($saisie->depenses && $saisie->depenses->count() > 0): ?>
                                        <table class="table table-hover table-striped">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th>#</th>
                                                    <th>Catégorie</th>
                                                    <th>Description</th>
                                                    <th class="text-center">Date</th>
                                                    <th class="text-right">Montant</th>
                                                    <th class="text-center">Justificatif</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $totalDepensesDetail = 0; ?>
                                                <?php $__currentLoopData = $saisie->depenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $depense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php $totalDepensesDetail += $depense->montant ?? 0; ?>
                                                    <tr>
                                                        <td><?php echo e($index + 1); ?></td>
                                                        <td>
                                                            <span class="badge badge-secondary">
                                                                <?php echo e($depense->categorie ?? 'Non catégorisé'); ?>

                                                            </span>
                                                        </td>
                                                        <td><?php echo e($depense->description ?? 'Sans description'); ?></td>
                                                        <td class="text-center">
                                                            <?php echo e($depense->created_at->format('d/m/Y H:i')); ?>

                                                        </td>
                                                        <td class="text-right text-danger font-weight-bold">
                                                            <?php echo e(number_format($depense->montant ?? 0, 0, ',', ' ')); ?> FCFA
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if($depense->justificatif): ?>
                                                                <a href="<?php echo e(asset('storage/' . $depense->justificatif)); ?>" 
                                                                   target="_blank" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-file-invoice"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">Aucun</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <!-- Total ligne -->
                                                <tr class="bg-gray">
                                                    <td colspan="4" class="text-right"><strong>TOTAL DÉPENSES:</strong></td>
                                                    <td class="text-right font-weight-bold text-danger">
                                                        <?php echo e(number_format($totalDepensesDetail, 0, ',', ' ')); ?> FCFA
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Aucune dépense enregistrée</h5>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes et observations -->
                    <?php if($saisie->notes_validation): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-sticky-note mr-2"></i> Notes de Validation
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <?php echo e($saisie->notes_validation); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Boutons d'action -->
                    <?php if($saisie->statut === 'en_attente'): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-footer text-center bg-light">
                                    <a href="<?php echo e(route('chief.validations')); ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
                                    </a>
                                    
                                    <button type="button" class="btn btn-success btn-validate" 
                                            data-id="<?php echo e($saisie->id); ?>">
                                        <i class="fas fa-check-circle mr-2"></i> Valider la saisie
                                    </button>
                                    
                                    <button type="button" class="btn btn-danger btn-reject" 
                                            data-id="<?php echo e($saisie->id); ?>">
                                        <i class="fas fa-times-circle mr-2"></i> Rejeter la saisie
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                Cette saisie a déjà été 
                                <?php if($saisie->statut === 'valide'): ?>
                                    <strong class="text-success">validée</strong>
                                    <?php if($saisie->validateur): ?>
                                        par <?php echo e($saisie->validateur->name); ?>

                                        le <?php echo e($saisie->validation_date->format('d/m/Y H:i')); ?>

                                    <?php endif; ?>
                                <?php elseif($saisie->statut === 'rejete'): ?>
                                    <strong class="text-danger">rejetée</strong>
                                    <?php if($saisie->validateur): ?>
                                        par <?php echo e($saisie->validateur->name); ?>

                                        le <?php echo e($saisie->validation_date->format('d/m/Y H:i')); ?>

                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo e(route('chief.validations')); ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left mr-2"></i> Retour aux validations
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de validation -->
<div class="modal fade" id="validateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="validateForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle mr-2"></i> Valider la saisie
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="validateComment">Commentaire (optionnel):</label>
                        <textarea class="form-control" id="validateComment" name="comment" 
                                  rows="3" placeholder="Ajouter un commentaire..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        En validant cette saisie, vous confirmez que tous les montants sont corrects.
                        Cette action est définitive.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-2"></i> Confirmer la validation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de rejet -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle mr-2"></i> Rejeter la saisie
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectReason">Raison du rejet *</label>
                        <textarea class="form-control" id="rejectReason" name="raison_rejet" 
                                  rows="3" required placeholder="Indiquer la raison du rejet..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Cette action est irréversible. La saisie sera marquée comme rejetée.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-2"></i> Confirmer le rejet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .card-header.bg-info, .card-header.bg-danger {
        color: white;
    }
    .bg-gray {
        background-color: #f8f9fa;
    }
    .table td, .table th {
        vertical-align: middle;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Gestion de la validation
    $('.btn-validate').on('click', function() {
        var saisieId = $(this).data('id');
        var url = '<?php echo e(route("chief.validation.valider", ":id")); ?>'.replace(':id', saisieId);
        $('#validateForm').attr('action', url);
        $('#validateModal').modal('show');
    });
    
    // Gestion du rejet
    $('.btn-reject').on('click', function() {
        var saisieId = $(this).data('id');
        var url = '<?php echo e(route("chief.validation.rejeter", ":id")); ?>'.replace(':id', saisieId);
        $('#rejectForm').attr('action', url);
        $('#rejectModal').modal('show');
    });
    
    // Validation des formulaires
    $('#validateForm').on('submit', function(e) {
        if (!confirm('Êtes-vous sûr de vouloir valider cette saisie ?')) {
            e.preventDefault();
        }
    });
    
    $('#rejectForm').on('submit', function(e) {
        var reason = $('#rejectReason').val().trim();
        if (!reason) {
            e.preventDefault();
            alert('Veuillez indiquer la raison du rejet.');
            $('#rejectReason').focus();
        } else if (!confirm('Êtes-vous sûr de vouloir rejeter cette saisie ?')) {
            e.preventDefault();
        }
    });
    
    // Afficher/masquer les détails
    $('.btn-toggle-details').on('click', function() {
        var target = $(this).data('target');
        $(target).slideToggle();
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.chief', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/chief/validation-show.blade.php ENDPATH**/ ?>


<?php $__env->startSection('title', 'Validations en Attente'); ?>
<?php $__env->startSection('page-icon', 'fa-clipboard-check'); ?>
<?php $__env->startSection('page-title', 'Validations'); ?>
<?php $__env->startSection('page-subtitle', 'Gestion des validations de saisies'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('chief.dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item active">Validations</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i> Filtre de station
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('chief.validations')); ?>" id="filterForm">
                    <div class="form-row align-items-center">
                        <div class="col-md-5 mb-2">
                            <label for="station_id" class="mr-2">Filtrer par station:</label>
                            <select name="station_id" id="station_id" class="form-control">
                                <option value="">Toutes les stations</option>
                                <?php $__currentLoopData = $allStations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $station): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($station->id); ?>" 
                                            <?php echo e(request('station_id') == $station->id || session('selected_station_id') == $station->id ? 'selected' : ''); ?>>
                                        <?php echo e($station->nom); ?> (<?php echo e($station->code); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Appliquer
                            </button>
                            
                            <?php if(request('station_id') || session('selected_station_id')): ?>
                                <a href="<?php echo e(route('chief.validations')); ?>" class="btn btn-secondary ml-2">
                                    <i class="fas fa-times"></i> Effacer
                                </a>
                            <?php endif; ?>
                            
                            <!-- Bouton pour revenir à la station du dashboard -->
                            <?php if(session('dashboard_station_id')): ?>
                                <?php
                                    $dashboardStation = \App\Models\Station::find(session('dashboard_station_id'));
                                ?>
                                <?php if($dashboardStation): ?>
                                    <a href="<?php echo e(route('chief.validations', ['station_id' => $dashboardStation->id])); ?>" 
                                       class="btn btn-info ml-2">
                                        <i class="fas fa-tachometer-alt"></i> Station Dashboard
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-3 mb-2 text-right">
                            <span class="badge badge-warning p-2">
                                <i class="fas fa-clock"></i> <?php echo e($saisies->total()); ?> validation(s) en attente
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list mr-2"></i> Liste des saisies à valider
                </h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="text" id="searchInput" class="form-control float-right" placeholder="Rechercher...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if($saisies->count() > 0): ?>
                    <table class="table table-hover text-nowrap" id="validationsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Station</th>
                                <th>Shift</th>
                                <th>Manager</th>
                                <th>Ventes (FCFA)</th>
                                <th>Écart</th>
                                <th>Dépenses</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $saisies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saisie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $ecartColor = $saisie->ecart_final > 0 ? 'success' : 
                                                 ($saisie->ecart_final < 0 ? 'danger' : 'secondary');
                                ?>
                                <tr>
                                    <td><?php echo e($saisie->id); ?></td>
                                    <td>
                                        <strong><?php echo e($saisie->date_shift->format('d/m/Y')); ?></strong><br>
                                        <small class="text-muted"><?php echo e($saisie->shift); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo e($saisie->station->nom ?? 'N/A'); ?></strong><br>
                                        <small class="text-muted"><?php echo e($saisie->station->code ?? ''); ?></small>
                                    </td>
                                    <td><?php echo e($saisie->shift ?? 'N/A'); ?></td>
                                    <td><?php echo e($saisie->user->name ?? $saisie->responsable); ?></td>
                                    <td class="font-weight-bold text-primary">
                                        <?php echo e(number_format($saisie->total_ventes, 0, ',', ' ')); ?>

                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo e($ecartColor); ?>">
                                            <?php echo e(number_format($saisie->ecart_final, 0, ',', ' ')); ?>

                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-danger">
                                        <?php echo e(number_format($saisie->total_depenses, 0, ',', ' ')); ?>

                                    </td>
                                    <td>
                                        <?php echo e($saisie->created_at->format('d/m H:i')); ?><br>
                                        <small class="text-muted"><?php echo e($saisie->created_at->diffForHumans()); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo e(route('chief.validation.show', $saisie->id)); ?>" 
                                               class="btn btn-info" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-success btn-validate" 
                                                    data-id="<?php echo e($saisie->id); ?>" title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-reject" 
                                                    data-id="<?php echo e($saisie->id); ?>" title="Rejeter">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h4 class="text-muted">Aucune validation en attente</h4>
                        <p class="text-muted">
                            <?php if(request('station_id') || session('selected_station_id')): ?>
                                Toutes les saisies de cette station ont été traitées.
                            <?php else: ?>
                                Toutes les saisies ont été validées ou rejetées.
                            <?php endif; ?>
                        </p>
                        <a href="<?php echo e(route('chief.dashboard')); ?>" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt mr-2"></i> Retour au Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if($saisies->count() > 0): ?>
            <div class="card-footer clearfix">
                <div class="float-left">
                    <span class="text-muted">
                        Affichage de <?php echo e($saisies->firstItem()); ?> à <?php echo e($saisies->lastItem()); ?> sur <?php echo e($saisies->total()); ?> saisies
                    </span>
                </div>
                <div class="float-right">
                    <?php echo e($saisies->appends(request()->except('page'))->links()); ?>

                </div>
            </div>
            <?php endif; ?>
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
                        <i class="fas fa-info-circle"></i> Cette action marquera la saisie comme validée.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer la validation</button>
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
                        <i class="fas fa-exclamation-triangle"></i> Cette action est irréversible.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Recherche dans le tableau
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#validationsTable tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(value) > -1);
        });
    });
    
    $('#searchBtn').on('click', function() {
        $('#searchInput').trigger('keyup');
    });
    
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
    
    // Auto-refresh toutes les 30 secondes
    setInterval(function() {
        $.ajax({
            url: '<?php echo e(route("chief.pending-count")); ?>',
            type: 'GET',
            data: {
                station_id: '<?php echo e(request('station_id') ?? session('selected_station_id')); ?>'
            },
            success: function(data) {
                if (data.count != <?php echo e($saisies->count()); ?>) {
                    location.reload();
                }
            }
        });
    }, 30000); // 30 secondes
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.chief', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\LENOVO\station-gestion-app-clean\resources\views/chief/validations.blade.php ENDPATH**/ ?>
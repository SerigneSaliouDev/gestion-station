@extends('layouts.admin')

@section('title', 'Corrections de Données')
@section('page-title', 'Supervision - Corrections de Données')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.supervision.data.corrections') }}">Supervision</a></li>
    <li class="breadcrumb-item active">Corrections de données</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Corrections de Données en Attente</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques des corrections -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">En attente</span>
                                    <span class="info-box-number">15</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">Corrections à traiter</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Traitées</span>
                                    <span class="info-box-number">42</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">Ce mois</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">En retard</span>
                                    <span class="info-box-number">3</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">> 7 jours</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-danger">
                                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Rejetées</span>
                                    <span class="info-box-number">8</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">Ce mois</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table des corrections -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Liste des demandes de correction</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newCorrectionModal">
                                    <i class="fas fa-plus mr-2"></i>Nouvelle correction
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Station</th>
                                            <th>Demandeur</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($i = 1; $i <= 10; $i++)
                                        @php
                                            $statuses = ['pending', 'in_progress', 'completed', 'rejected'];
                                            $status = $statuses[array_rand($statuses)];
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'in_progress' => 'info',
                                                'completed' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                            $statusTexts = [
                                                'pending' => 'En attente',
                                                'in_progress' => 'En cours',
                                                'completed' => 'Traité',
                                                'rejected' => 'Rejeté'
                                            ];
                                        @endphp
                                        <tr>
                                            <td>#CORR{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ now()->subDays(rand(0, 30))->format('d/m/Y') }}</td>
                                            <td>
                                                @php
                                                    $types = ['Ventes', 'Stock', 'Prix', 'Utilisateur', 'Station'];
                                                    echo $types[array_rand($types)];
                                                @endphp
                                            </td>
                                            <td>
                                                Correction de données {{ $i }} - 
                                                <small class="text-muted">
                                                    {{ ['Écart de comptage', 'Erreur de saisie', 'Données manquantes', 'Incohérence'][array_rand([0,1,2,3])] }}
                                                </small>
                                            </td>
                                            <td>Station {{ chr(64 + $i) }}</td>
                                            <td>Manager {{ $i }}</td>
                                            <td>
                                                <span class="badge badge-{{ $statusColors[$status] }}">
                                                    {{ $statusTexts[$status] }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($status == 'pending')
                                                <button class="btn btn-sm btn-success" title="Accepter">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" title="Rejeter">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <ul class="pagination pagination-sm m-0 float-right">
                                <li class="page-item"><a class="page-link" href="#">«</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">»</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Graphique d'activité -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Activité des corrections</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="correctionsActivityChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Statistiques par type</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="correctionsTypeChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouvelle Correction -->
<div class="modal fade" id="newCorrectionModal" tabindex="-1" role="dialog" aria-labelledby="newCorrectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="newCorrectionModalLabel">Nouvelle correction de données</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
            <form id="newCorrectionForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="correctionType">Type de correction</label>
                                <select class="form-control" id="correctionType" name="type" required>
                                    <option value="">Sélectionnez un type</option>
                                    <option value="sales">Correction de ventes</option>
                                    <option value="stock">Correction de stock</option>
                                    <option value="price">Correction de prix</option>
                                    <option value="user">Correction utilisateur</option>
                                    <option value="station">Correction station</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="correctionStation">Station concernée</label>
                                <select class="form-control" id="correctionStation" name="station_id">
                                    <option value="">Toutes les stations</option>
                                    @foreach($stations ?? [] as $station)
                                    <option value="{{ $station->id }}">{{ $station->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="correctionDescription">Description détaillée</label>
                        <textarea class="form-control" id="correctionDescription" name="description" rows="3" 
                                  placeholder="Décrivez en détail la correction nécessaire..." required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="correctionDate">Date concernée</label>
                                <input type="date" class="form-control" id="correctionDate" name="date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="correctionPriority">Priorité</label>
                                <select class="form-control" id="correctionPriority" name="priority" required>
                                    <option value="low">Basse</option>
                                    <option value="medium" selected>Moyenne</option>
                                    <option value="high">Haute</option>
                                    <option value="critical">Critique</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Justificatif</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="correctionFile" name="file">
                            <label class="custom-file-label" for="correctionFile">Choisir un fichier</label>
                        </div>
                        <small class="form-text text-muted">Format acceptés: PDF, JPG, PNG, Excel (max 5MB)</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Information</h6>
                        <small>
                            Les corrections soumises seront examinées par l'équipe de supervision.
                            Vous serez notifié par email lorsque la correction sera traitée.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Soumettre la correction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Graphique d'activité
        var activityCtx = document.getElementById('correctionsActivityChart').getContext('2d');
        var activityChart = new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: @json(collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('d/m'))),
                datasets: [{
                    label: 'Corrections créées',
                    data: [3, 5, 2, 6, 4, 7, 3],
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 2,
                    fill: true
                }, {
                    label: 'Corrections traitées',
                    data: [2, 4, 3, 5, 3, 6, 2],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        // Graphique par type
        var typeCtx = document.getElementById('correctionsTypeChart').getContext('2d');
        var typeChart = new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Ventes', 'Stock', 'Prix', 'Utilisateur', 'Autre'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: ['#dc3545', '#ffc107', '#17a2b8', '#6610f2', '#6c757d'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
        
        // Gestion du formulaire de nouvelle correction
        $('#newCorrectionForm').on('submit', function(e) {
            e.preventDefault();
            
            // Ici, vous enverriez les données au serveur
            alert('Correction soumise avec succès ! Elle sera traitée dans les plus brefs délais.');
            $('#newCorrectionModal').modal('hide');
            $('#newCorrectionForm')[0].reset();
        });
        
        // Gestion de l'upload de fichier
        $('#correctionFile').on('change', function(e) {
            var fileName = e.target.files[0].name;
            $(this).next('.custom-file-label').html(fileName);
        });
        
        // Définir la date d'aujourd'hui par défaut
        $('#correctionDate').val(new Date().toISOString().split('T')[0]);
    });
</script>
@endpush
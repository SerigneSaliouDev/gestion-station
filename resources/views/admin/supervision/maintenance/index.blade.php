@extends('layouts.admin')

@section('title', 'Maintenance - Supervision')
@section('page-title', 'Supervision - Maintenance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.supervision.maintenance.index') }}">Supervision</a></li>
    <li class="breadcrumb-item active">Maintenance</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Gestion de la Maintenance</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#newMaintenanceModal">
                            <i class="fas fa-plus mr-2"></i>Nouvelle intervention
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-tools"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">En cours</span>
                                    <span class="info-box-number">8</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 60%"></div>
                                    </div>
                                    <span class="progress-description">Interventions actives</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Planifiées</span>
                                    <span class="info-box-number">12</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 40%"></div>
                                    </div>
                                    <span class="progress-description">À venir</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Terminées</span>
                                    <span class="info-box-number">45</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 90%"></div>
                                    </div>
                                    <span class="progress-description">Ce mois</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-danger">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Urgentes</span>
                                    <span class="info-box-number">3</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 20%"></div>
                                    </div>
                                    <span class="progress-description">Nécessitent attention</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglets -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header p-0">
                                    <ul class="nav nav-tabs" id="maintenanceTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="active-tab" data-toggle="tab" href="#active" role="tab">
                                                <i class="fas fa-tools mr-2"></i>En cours (8)
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="scheduled-tab" data-toggle="tab" href="#scheduled" role="tab">
                                                <i class="fas fa-calendar-alt mr-2"></i>Planifiées (12)
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="completed-tab" data-toggle="tab" href="#completed" role="tab">
                                                <i class="fas fa-check-circle mr-2"></i>Terminées (45)
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="equipment-tab" data-toggle="tab" href="#equipment" role="tab">
                                                <i class="fas fa-gas-pump mr-2"></i>Équipements
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content" id="maintenanceTabsContent">
                                        <!-- Onglet En cours -->
                                        <div class="tab-pane fade show active" id="active" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Équipement</th>
                                                            <th>Station</th>
                                                            <th>Type</th>
                                                            <th>Début</th>
                                                            <th>Priorité</th>
                                                            <th>Technicien</th>
                                                            <th>Progression</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @for($i = 1; $i <= 8; $i++)
                                                        @php
                                                            $priority = ['low', 'medium', 'high', 'critical'][rand(0, 3)];
                                                            $priorityColors = [
                                                                'low' => 'info',
                                                                'medium' => 'primary',
                                                                'high' => 'warning',
                                                                'critical' => 'danger'
                                                            ];
                                                            $priorityTexts = [
                                                                'low' => 'Basse',
                                                                'medium' => 'Moyenne',
                                                                'high' => 'Haute',
                                                                'critical' => 'Critique'
                                                            ];
                                                            $progress = rand(20, 90);
                                                        @endphp
                                                        <tr>
                                                            <td>#MNT{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</td>
                                                            <td>
                                                                <strong>@php echo ['Pompe Essence', 'Distributeur', 'Cuve', 'Système électrique'][rand(0, 3)]; @endphp</strong><br>
                                                                <small>ID: EQP{{ rand(100, 999) }}</small>
                                                            </td>
                                                            <td>Station {{ chr(64 + $i) }}</td>
                                                            <td>
                                                                <span class="badge badge-{{ ['success', 'warning', 'info'][rand(0, 2)] }}">
                                                                    @php echo ['Préventive', 'Corrective', 'Prédictive'][rand(0, 2)]; @endphp
                                                                </span>
                                                            </td>
                                                            <td>{{ now()->subDays(rand(0, 5))->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                <span class="badge badge-{{ $priorityColors[$priority] }}">
                                                                    {{ $priorityTexts[$priority] }}
                                                                </span>
                                                            </td>
                                                            <td>Technicien {{ $i }}</td>
                                                            <td>
                                                                <div class="progress progress-xs">
                                                                    <div class="progress-bar bg-{{ $progress >= 80 ? 'success' : ($progress >= 50 ? 'warning' : 'info') }}" 
                                                                         style="width: {{ $progress }}%"></div>
                                                                </div>
                                                                <small class="text-muted">{{ $progress }}%</small>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-info" title="Détails">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-success" title="Mettre à jour">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger" title="Annuler">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @endfor
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <!-- Onglet Planifiées -->
                                        <div class="tab-pane fade" id="scheduled" role="tabpanel">
                                            <p>Liste des maintenances planifiées...</p>
                                        </div>
                                        
                                        <!-- Onglet Terminées -->
                                        <div class="tab-pane fade" id="completed" role="tabpanel">
                                            <p>Historique des maintenances terminées...</p>
                                        </div>
                                        
                                        <!-- Onglet Équipements -->
                                        <div class="tab-pane fade" id="equipment" role="tabpanel">
                                            <p>État des équipements...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Carte géographique des interventions -->
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Carte des interventions</h3>
                                </div>
                                <div class="card-body">
                                    <div id="maintenanceMap" style="height: 400px; background: #f8f9fa; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-map-marked-alt fa-3x mb-3"></i>
                                            <h5>Carte des interventions en maintenance</h5>
                                            <p>Intégration avec Google Maps ou OpenStreetMap</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Statistiques rapides</h3>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Maintenance préventive
                                            <span class="badge badge-primary badge-pill">65%</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Maintenance corrective
                                            <span class="badge badge-warning badge-pill">25%</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Maintenance prédictive
                                            <span class="badge badge-info badge-pill">10%</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Temps moyen d'intervention
                                            <span class="badge badge-secondary badge-pill">4.5h</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Coût moyen
                                            <span class="badge badge-dark badge-pill">125 000 FCFA</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <h6>Équipements les plus défaillants :</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-pump mr-2 text-danger"></i>Pompes essence (42%)</li>
                                            <li><i class="fas fa-tachometer-alt mr-2 text-warning"></i>Distributeurs (28%)</li>
                                            <li><i class="fas fa-oil-can mr-2 text-info"></i>Cuves (15%)</li>
                                            <li><i class="fas fa-bolt mr-2 text-primary"></i>Électricité (10%)</li>
                                            <li><i class="fas fa-cogs mr-2 text-secondary"></i>Autres (5%)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouvelle Intervention -->
<div class="modal fade" id="newMaintenanceModal" tabindex="-1" role="dialog" aria-labelledby="newMaintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="newMaintenanceModalLabel">Nouvelle intervention de maintenance</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
            <form id="newMaintenanceForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maintenanceStation">Station</label>
                                <select class="form-control" id="maintenanceStation" name="station_id" required>
                                    <option value="">Sélectionnez une station</option>
                                    @foreach($stations ?? [] as $station)
                                    <option value="{{ $station->id }}">{{ $station->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maintenanceEquipment">Équipement</label>
                                <select class="form-control" id="maintenanceEquipment" name="equipment_id" required>
                                    <option value="">Sélectionnez un équipement</option>
                                    <option value="pump">Pompe à essence</option>
                                    <option value="dispenser">Distributeur</option>
                                    <option value="tank">Cuve de stockage</option>
                                    <option value="electrical">Système électrique</option>
                                    <option value="other">Autre équipement</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maintenanceType">Type de maintenance</label>
                                <select class="form-control" id="maintenanceType" name="type" required>
                                    <option value="preventive">Préventive</option>
                                    <option value="corrective">Corrective</option>
                                    <option value="predictive">Prédictive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maintenancePriority">Priorité</label>
                                <select class="form-control" id="maintenancePriority" name="priority" required>
                                    <option value="low">Basse</option>
                                    <option value="medium" selected>Moyenne</option>
                                    <option value="high">Haute</option>
                                    <option value="critical">Critique</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="maintenanceDescription">Description du problème / intervention</label>
                        <textarea class="form-control" id="maintenanceDescription" name="description" rows="3" required
                                  placeholder="Décrivez le problème ou l'intervention prévue..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maintenanceStart">Date de début prévue</label>
                                <input type="datetime-local" class="form-control" id="maintenanceStart" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maintenanceDuration">Durée estimée (heures)</label>
                                <input type="number" class="form-control" id="maintenanceDuration" name="duration" min="1" max="72" value="4">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="maintenanceTechnician">Technicien assigné</label>
                        <select class="form-control" id="maintenanceTechnician" name="technician_id">
                            <option value="">Non assigné</option>
                            @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">Technicien {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Attention</h6>
                        <small>
                            Une maintenance critique peut nécessiter l'arrêt temporaire de la station.
                            Assurez-vous de planifier pendant les heures creuses.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Planifier l'intervention</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialiser les onglets
        $('#maintenanceTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
        
        // Gestion du formulaire de nouvelle maintenance
        $('#newMaintenanceForm').on('submit', function(e) {
            e.preventDefault();
            
            // Récupérer les valeurs
            var station = $('#maintenanceStation').val();
            var equipment = $('#maintenanceEquipment').val();
            var type = $('#maintenanceType').val();
            var description = $('#maintenanceDescription').val();
            
            if (!station || !equipment || !description) {
                alert('Veuillez remplir tous les champs obligatoires.');
                return;
            }
            
            // Ici, vous enverriez les données au serveur
            alert('Intervention de maintenance planifiée avec succès !');
            $('#newMaintenanceModal').modal('hide');
            $('#newMaintenanceForm')[0].reset();
            
            // Simuler un rechargement des données
            setTimeout(function() {
                location.reload();
            }, 1500);
        });
        
        // Définir la date et heure de début par défaut (demain 8h)
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(8, 0, 0, 0);
        
        var formattedDate = tomorrow.toISOString().slice(0, 16);
        $('#maintenanceStart').val(formattedDate);
        
        // Gestion du changement de priorité
        $('#maintenancePriority').on('change', function() {
            var priority = $(this).val();
            var alertDiv = $('.alert-warning');
            
            if (priority === 'critical' || priority === 'high') {
                alertDiv.show();
            } else {
                alertDiv.hide();
            }
        });
        
        // Initialiser la carte (simulée)
        $('#maintenanceMap').on('click', function() {
            alert('Fonctionnalité de carte à intégrer avec Google Maps API');
        });
    });
</script>
@endpush
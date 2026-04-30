@extends('layouts.admin')

@section('title', 'Maintenance - Supervision')
@section('page-title', 'Supervision - Maintenance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Admin</a></li>
    <li class="breadcrumb-item active">Maintenance</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Tableau de Bord de Supervision</h3>
                </div>
                <div class="card-body">
                    <!-- Statistiques RÉELLES -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Stations</span>
                                    <span class="info-box-number">{{ $stats['total_stations'] ?? 0 }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ (($stats['active_stations'] ?? 0)/($stats['total_stations'] ?? 1))*100 }}%"></div>
                                    </div>
                                    <span class="progress-description">{{ $stats['active_stations'] ?? 0 }} active(s)</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Validations en attente</span>
                                    <span class="info-box-number">{{ $stats['pending_validations'] ?? 0 }}</span>
                                    <div class="progress">
                                        @php
                                            $pendingPercent = min(100, (($stats['pending_validations'] ?? 0)/50)*100);
                                        @endphp
                                        <div class="progress-bar" style="width: {{ $pendingPercent }}%"></div>
                                    </div>
                                    <span class="progress-description">Requièrent attention</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Shifts ce mois</span>
                                    <span class="info-box-number">{{ $stats['shifts_month'] ?? 0 }}</span>
                                    <div class="progress">
                                        @php
                                            $shiftsPercent = min(100, (($stats['shifts_month'] ?? 0)/100)*100);
                                        @endphp
                                        <div class="progress-bar" style="width: {{ $shiftsPercent }}%"></div>
                                    </div>
                                    <span class="progress-description">{{ $stats['shifts_today'] ?? 0 }} aujourd'hui</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box bg-gradient-danger">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Corrections en attente</span>
                                    <span class="info-box-number">{{ $stats['pending_corrections'] ?? 0 }}</span>
                                    <div class="progress">
                                        @php
                                            $correctionsPercent = min(100, (($stats['pending_corrections'] ?? 0)/20)*100);
                                        @endphp
                                        <div class="progress-bar" style="width: {{ $correctionsPercent }}%"></div>
                                    </div>
                                    <span class="progress-description">Données à corriger</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Performance des stations RÉELLES -->
                    @if(isset($stations) && $stations->count() > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Performance des Stations</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($stations as $station)
                                        @php
                                            // Calculer les statistiques pour chaque station
                                            $shifts = $station->shifts ?? collect();
                                            $totalShifts = $shifts->count();
                                            $validatedShifts = $shifts->where('statut', 'valide')->count();
                                            $pendingShifts = $shifts->where('statut', 'en_attente')->count();
                                            $rejectedShifts = $shifts->where('statut', 'rejete')->count();
                                            
                                            $validationRate = $totalShifts > 0 ? ($validatedShifts / $totalShifts) * 100 : 0;
                                            
                                            // Déterminer le statut
                                            $statusText = 'Normal';
                                            $statusColor = 'success';
                                            
                                            if ($pendingShifts > 3) {
                                                $statusText = 'Attention';
                                                $statusColor = 'warning';
                                            }
                                            if ($validationRate < 70) {
                                                $statusText = 'Critique';
                                                $statusColor = 'danger';
                                            }
                                            if ($pendingShifts > 0 && $validationRate < 50) {
                                                $statusText = 'Urgent';
                                                $statusColor = 'danger';
                                            }
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="card mb-3 border-{{ $statusColor }}">
                                                <div class="card-header bg-{{ $statusColor }}">
                                                    <h3 class="card-title text-white">
                                                        <i class="fas fa-gas-pump mr-2"></i>{{ $station->nom ?? 'N/A' }}
                                                    </h3>
                                                    <div class="card-tools">
                                                        <span class="badge badge-light">{{ $station->code ?? '' }}</span>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-8">
                                                            <p class="mb-1">
                                                                <i class="fas fa-chart-line mr-2 text-info"></i>
                                                                Total shifts: <strong>{{ $totalShifts }}</strong>
                                                            </p>
                                                            <p class="mb-1">
                                                                <i class="fas fa-check-circle mr-2 text-success"></i>
                                                                Validés: <strong>{{ $validatedShifts }}</strong>
                                                                ({{ round($validationRate) }}%)
                                                            </p>
                                                            <p class="mb-1">
                                                                <i class="fas fa-clock mr-2 text-warning"></i>
                                                                En attente: <strong>{{ $pendingShifts }}</strong>
                                                            </p>
                                                            <p class="mb-0">
                                                                <i class="fas fa-times-circle mr-2 text-danger"></i>
                                                                Rejetés: <strong>{{ $rejectedShifts }}</strong>
                                                            </p>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            <div class="sparkpie" data-percent="{{ round($validationRate) }}">
                                                                <canvas width="80" height="80"></canvas>
                                                            </div>
                                                            <p class="mt-2 mb-0">
                                                                <span class="badge badge-{{ $statusColor }}">
                                                                    {{ $statusText }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        @if($pendingShifts > 0)
                                                        <div class="alert alert-warning py-2 mb-2">
                                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                                            {{ $pendingShifts }} shift(s) en attente de validation
                                                        </div>
                                                        @endif
                                                        
                                                        @if($validationRate < 70)
                                                        <div class="alert alert-danger py-2 mb-0">
                                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                                            Taux de validation faible ({{ round($validationRate) }}%)
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <a href="{{ route('admin.stations.show', $station->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye mr-1"></i> Détails
                                                    </a>
                                                    @if($pendingShifts > 0)
                                                    <span class="badge badge-danger float-right">
                                                        {{ $pendingShifts }} en attente
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Onglets avec données RÉELLES -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header p-0">
                                    <ul class="nav nav-tabs" id="supervisionTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="corrections-tab" data-toggle="tab" href="#corrections" role="tab">
                                                <i class="fas fa-wrench mr-2"></i>Corrections
                                                @if(isset($pendingCorrections) && $pendingCorrections->count() > 0)
                                                <span class="badge badge-warning ml-1">{{ $pendingCorrections->count() }}</span>
                                                @endif
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="problems-tab" data-toggle="tab" href="#problems" role="tab">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>Problèmes détectés
                                                @if(isset($problematicShifts) && $problematicShifts->count() > 0)
                                                <span class="badge badge-danger ml-1">{{ $problematicShifts->count() }}</span>
                                                @endif
                                            </a>
                                        </li>
                                        @if(isset($maintenances) && $maintenances->count() > 0)
                                        <li class="nav-item">
                                            <a class="nav-link" id="maintenance-tab" data-toggle="tab" href="#maintenance" role="tab">
                                                <i class="fas fa-tools mr-2"></i>Maintenances
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content" id="supervisionTabsContent">
                                        <!-- Onglet Corrections RÉELLES -->
                                        <div class="tab-pane fade show active" id="corrections" role="tabpanel">
                                            @if(isset($pendingCorrections) && $pendingCorrections->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-hover table-sm">
                                                    <thead class="thead-dark">
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Type</th>
                                                            <th>Station</th>
                                                            <th>Description</th>
                                                            <th>Créé le</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($pendingCorrections as $correction)
                                                        <tr>
                                                            <td><strong>#{{ $correction->id }}</strong></td>
                                                            <td>
                                                                <span class="badge badge-info">
                                                                    {{ ucfirst($correction->correction_type ?? 'N/A') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $correction->station->nom ?? 'N/A' }}</td>
                                                            <td>{{ Str::limit($correction->reason ?? '', 60) }}</td>
                                                            <td>{{ $correction->created_at->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                @if(Route::has('admin.corrections.show'))
                                                                <a href="{{ route('admin.corrections.show', $correction->id) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                @endif
                                                                @if(Route::has('admin.corrections.resolve'))
                                                                <a href="{{ route('admin.corrections.resolve', $correction->id) }}" class="btn btn-sm btn-success">
                                                                    <i class="fas fa-check"></i>
                                                                </a>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <div class="alert alert-info text-center py-4">
                                                <i class="fas fa-info-circle fa-2x mb-3 text-info"></i>
                                                <h5>Aucune correction en attente</h5>
                                                <p class="mb-0">Toutes les corrections ont été traitées</p>
                                            </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Onglet Problèmes détectés RÉELS -->
                                        <div class="tab-pane fade" id="problems" role="tabpanel">
                                            @if(isset($problematicShifts) && $problematicShifts->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-hover table-sm">
                                                    <thead class="thead-dark">
                                                        <tr>
                                                            <th>ID Shift</th>
                                                            <th>Station</th>
                                                            <th>Date</th>
                                                            <th>Écart (FCFA)</th>
                                                            <th>Problème</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($problematicShifts as $shift)
                                                        @php
                                                            $problem = $shift->ecart_final < -10000 ? 'Écart négatif important' : 'Écart positif important';
                                                            $badgeClass = $shift->ecart_final < -10000 ? 'danger' : 'warning';
                                                        @endphp
                                                        <tr>
                                                            <td><strong>#{{ $shift->id }}</strong></td>
                                                            <td>{{ $shift->station->nom ?? 'N/A' }}</td>
                                                            <td>{{ $shift->date_shift->format('d/m/Y') }}</td>
                                                            <td class="text-right">
                                                                <span class="badge badge-{{ $badgeClass }}">
                                                                    {{ number_format($shift->ecart_final, 0, ',', ' ') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $problem }}</td>
                                                            <td>
                                                                @if(Route::has('admin.validations.show'))
                                                                <a href="{{ route('admin.validations.show', $shift->id) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> Vérifier
                                                                </a>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <div class="alert alert-success text-center py-4">
                                                <i class="fas fa-check-circle fa-2x mb-3 text-success"></i>
                                                <h5>Aucun problème détecté</h5>
                                                <p class="mb-0">Tous les shifts sont dans les normes acceptables</p>
                                            </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Onglet Maintenances (si modèle créé) -->
                                        @if(isset($maintenances) && $maintenances->count() > 0)
                                        <div class="tab-pane fade" id="maintenance" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-sm">
                                                    <thead class="thead-dark">
                                                        <tr>
                                                            <th>Titre</th>
                                                            <th>Station</th>
                                                            <th>Type</th>
                                                            <th>Priorité</th>
                                                            <th>Statut</th>
                                                            <th>Date prévue</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($maintenances as $maintenance)
                                                        @php
                                                            $priorityColors = [
                                                                'low' => 'info',
                                                                'medium' => 'primary',
                                                                'high' => 'warning',
                                                                'critical' => 'danger'
                                                            ];
                                                            $statusColors = [
                                                                'pending' => 'warning',
                                                                'in_progress' => 'info',
                                                                'completed' => 'success',
                                                                'cancelled' => 'secondary'
                                                            ];
                                                        @endphp
                                                        <tr>
                                                            <td>{{ Str::limit($maintenance->title, 40) }}</td>
                                                            <td>{{ $maintenance->station->nom ?? 'N/A' }}</td>
                                                            <td>{{ ucfirst($maintenance->type ?? '') }}</td>
                                                            <td>
                                                                <span class="badge badge-{{ $priorityColors[$maintenance->priority ?? 'medium'] }}">
                                                                    {{ ucfirst($maintenance->priority ?? 'medium') }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-{{ $statusColors[$maintenance->status ?? 'pending'] }}">
                                                                    {{ ucfirst($maintenance->status ?? 'pending') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $maintenance->scheduled_date ? $maintenance->scheduled_date->format('d/m/Y') : 'Non planifié' }}</td>
                                                            <td>
                                                                <a href="#" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Graphiques avec données RÉELLES -->
                    <div class="row mt-4">
                        @if(isset($chartData))
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Répartition des shifts par statut</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="shiftsStatusChart" height="150"></canvas>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-{{ isset($chartData) ? '4' : '12' }}">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Statistiques rapides</h3>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        @if(isset($stations) && $stations->count() > 0)
                                            @foreach($stations as $station)
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">{{ $station->nom ?? 'N/A' }}</h6>
                                                        <small class="text-muted">{{ $station->code ?? '' }}</small>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="badge badge-info">
                                                            {{ $station->shifts->count() ?? 0 }} shifts
                                                        </span>
                                                    </div>
                                                </div>
                                                @php
                                                    $pending = $station->shifts->where('statut', 'en_attente')->count();
                                                    $validated = $station->shifts->where('statut', 'valide')->count();
                                                @endphp
                                                @if($pending > 0)
                                                <small class="text-warning">
                                                    <i class="fas fa-clock mr-1"></i>{{ $pending }} en attente
                                                </small>
                                                @endif
                                            </div>
                                            @endforeach
                                        @endif
                                        
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Total shifts ce mois</span>
                                                <span class="badge badge-primary badge-pill">{{ $stats['shifts_month'] ?? 0 }}</span>
                                            </div>
                                        </div>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Shifts aujourd'hui</span>
                                                <span class="badge badge-success badge-pill">{{ $stats['shifts_today'] ?? 0 }}</span>
                                            </div>
                                        </div>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Taux de validation moyen</span>
                                                @php
                                                    $avgRate = 0;
                                                    if(isset($stations) && $stations->count() > 0) {
                                                        $avgRate = $stations->avg(function($station) {
                                                            $total = $station->shifts->count();
                                                            $validated = $station->shifts->where('statut', 'valide')->count();
                                                            return $total > 0 ? ($validated/$total)*100 : 0;
                                                        });
                                                    }
                                                @endphp
                                                <span class="badge badge-{{ $avgRate >= 80 ? 'success' : ($avgRate >= 60 ? 'warning' : 'danger') }} badge-pill">
                                                    {{ round($avgRate) }}%
                                                </span>
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
    </div>
</div>
@endsection

@push('styles')
<style>
    .sparkpie {
        position: relative;
        display: inline-block;
    }
    .sparkpie canvas {
        display: block;
        margin: 0 auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Initialiser les onglets
        $('#supervisionTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
        
        // Dessiner les pie charts pour les stations
        $('.sparkpie').each(function() {
            var percent = $(this).data('percent');
            var canvas = $(this).find('canvas')[0];
            var ctx = canvas.getContext('2d');
            
            // Déterminer la couleur en fonction du pourcentage
            var color = percent >= 80 ? '#28a745' : (percent >= 60 ? '#ffc107' : '#dc3545');
            
            // Dessiner le cercle
            var centerX = canvas.width / 2;
            var centerY = canvas.height / 2;
            var radius = 35;
            
            // Cercle de fond
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI, false);
            ctx.fillStyle = '#f8f9fa';
            ctx.fill();
            ctx.lineWidth = 2;
            ctx.strokeStyle = '#e9ecef';
            ctx.stroke();
            
            // Arc de progression
            var startAngle = -Math.PI / 2;
            var endAngle = startAngle + (2 * Math.PI * percent / 100);
            
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, startAngle, endAngle, false);
            ctx.lineWidth = 8;
            ctx.strokeStyle = color;
            ctx.stroke();
            
            // Texte du pourcentage
            ctx.fillStyle = color;
            ctx.font = 'bold 14px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(percent + '%', centerX, centerY);
        });
        
        // Graphique des shifts par statut
        @if(isset($chartData) && isset($chartData['shifts_by_status']))
        var shiftsCtx = document.getElementById('shiftsStatusChart').getContext('2d');
        var shiftsChart = new Chart(shiftsCtx, {
            type: 'doughnut',
            data: {
                labels: @json($chartData['shifts_by_status']['labels'] ?? []),
                datasets: [{
                    data: @json($chartData['shifts_by_status']['data'] ?? []),
                    backgroundColor: @json($chartData['shifts_by_status']['colors'] ?? []),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw + ' shift(s)';
                                return label;
                            }
                        }
                    }
                }
            }
        });
        @endif
    });
</script>
@endpush
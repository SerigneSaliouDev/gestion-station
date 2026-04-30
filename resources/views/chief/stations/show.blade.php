@extends('layouts.chief')

@section('title', 'Détails de la Station')
@section('page-icon', 'fa-eye')
@section('page-title', 'Détails de la Station')
@section('page-subtitle', 'Informations complètes et supervision')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('chief.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('chief.stations') }}">Stations</a></li>
    <li class="breadcrumb-item active">{{ $station->nom ?? 'Détails' }}</li>
@endsection

@section('stats')
@if(isset($station) && $station)
<div class="row">
    <!-- Ventes aujourd'hui -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info stats-card">
            <div class="inner">
                <h3 class="font-weight-bold">{{ number_format($todaySales ?? 0, 0, ',', ' ') }}</h3>
                <p class="mb-0 text-uppercase font-weight-bold">Ventes aujourd'hui</p>
                <small class="text-white-50">FCFA</small>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave stats-icon"></i>
            </div>
            <a href="{{ route('chief.rapports.stations', ['station_id' => $station->id, 'start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" 
               class="small-box-footer d-block text-center py-2 bg-dark bg-opacity-25">
                <i class="fas fa-eye mr-1"></i> Voir détails <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
    
    <!-- Shifts en attente -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning stats-card">
            <div class="inner">
                <h3 class="font-weight-bold">{{ $pendingShiftsCount ?? 0 }}</h3>
                <p class="mb-0 text-uppercase font-weight-bold">Validations en attente</p>
                <small class="text-white-50">shifts</small>
            </div>
            <div class="icon">
                <i class="fas fa-clock stats-icon"></i>
            </div>
            <a href="{{ route('chief.validations', ['station' => $station->id]) }}" 
               class="small-box-footer d-block text-center py-2 bg-dark bg-opacity-25">
                <i class="fas fa-eye mr-1"></i> Voir validations <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
    
    <!-- Shifts ce mois -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success stats-card">
            <div class="inner">
                <h3 class="font-weight-bold">{{ $monthShiftsCount ?? 0 }}</h3>
                <p class="mb-0 text-uppercase font-weight-bold">Shifts ce mois</p>
                <small class="text-white-50">cumul</small>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt stats-icon"></i>
            </div>
            <a href="{{ route('chief.rapports.stations', ['station_id' => $station->id, 'start_date' => now()->startOfMonth()->format('Y-m-d')]) }}" 
               class="small-box-footer d-block text-center py-2 bg-dark bg-opacity-25">
                <i class="fas fa-chart-bar mr-1"></i> Voir rapport <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
    
    <!-- Écart moyen -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-purple stats-card">
            <div class="inner">
                <h3 class="font-weight-bold">{{ number_format($avgEcart ?? 0, 0, ',', ' ') }}</h3>
                <p class="mb-0 text-uppercase font-weight-bold">Écart moyen</p>
                <small class="text-white-50">FCFA</small>
            </div>
            <div class="icon">
                <i class="fas fa-balance-scale stats-icon"></i>
            </div>
            <div class="small-box-footer bg-dark bg-opacity-25 text-center py-2">
                <i class="fas fa-chart-line mr-1"></i> Indicateur clé
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Carte principale des détails de la station -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-gas-pump mr-2"></i>
                        Station : <strong>{{ $station->nom }}</strong>
                        <span class="badge badge-secondary ml-2">{{ $station->code }}</span>
                        @if($station->code == 'A')
                            <span class="badge badge-warning ml-1">Pilote</span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('chief.dashboard', ['station_id' => $station->id]) }}" class="btn btn-tool text-info" title="Dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle mr-2"></i> Informations générales</h5>
                            <table class="table table-sm table-borderless">
                                
                                    <th width="40%">Code:</th>
                                    <td><span class="badge badge-secondary">{{ $station->code }}</span></td>
                                </tr>
                                
                                    <th>Ville:</th>
                                    <td>{{ $station->ville ?? 'Non spécifiée' }}</td>
                                </tr>
                                
                                    <th>Adresse:</th>
                                    <td>{{ $station->adresse ?? 'Non spécifiée' }}</td>
                                </tr>
                                
                                    <th>Téléphone:</th>
                                    <td>{{ $station->telephone ?? 'Non spécifié' }}</td>
                                </tr>
                                
                                    <th>Email:</th>
                                    <td>{{ $station->email ?? 'Non spécifié' }}</td>
                                </tr>
                                
                                    <th>Statut:</th>
                                    <td>
                                        <span class="badge badge-{{ $station->statut == 'actif' ? 'success' : 'danger' }}">
                                            {{ $station->statut == 'actif' ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                
                                    <th>Date création:</th>
                                    <td>{{ $station->created_at->format('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fas fa-chart-bar mr-2"></i> Capacités et stocks RÉELS</h5>
                            <table class="table table-sm table-borderless">
                                @php
                                    // Récupérer les stocks réels depuis la base de données
                                    $superTanks = $station->tanks()->where('fuel_type', 'super')->get();
                                    $gazoleTanks = $station->tanks()->whereIn('fuel_type', ['gasoil', 'gazole', 'diesel'])->get();
                                    
                                    $superCapacity = $superTanks->sum('capacity');
                                    $gazoleCapacity = $gazoleTanks->sum('capacity');
                                    
                                    $superCurrent = $superTanks->sum('current_volume');
                                    $gazoleCurrent = $gazoleTanks->sum('current_volume');
                                    
                                    $superPercent = $superCapacity > 0 ? round(($superCurrent / $superCapacity) * 100, 1) : 0;
                                    $gazolePercent = $gazoleCapacity > 0 ? round(($gazoleCurrent / $gazoleCapacity) * 100, 1) : 0;
                                @endphp
                                
                                <tr>
                                    <th width="50%">Capacité Super:</th>
                                    <td class="font-weight-bold">
                                        {{ number_format($superCapacity, 0, ',', ' ') }} L
                                    </td>
                                </tr>
                                <tr>
                                    <th>Capacité Gazole:</th>
                                    <td class="font-weight-bold">
                                        {{ number_format($gazoleCapacity, 0, ',', ' ') }} L
                                    </td>
                                </tr>
                                <tr>
                                    <th>Capacité Totale:</th>
                                    <td class="font-weight-bold text-primary">
                                        {{ number_format($superCapacity + $gazoleCapacity, 0, ',', ' ') }} L
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <hr>
                                        <small class="text-muted">Stocks actuels RÉELS :</small>
                                    </th>
                                </tr>
                                <tr>
                                    <th>Super actuel:</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 10px;">
                                                <div class="progress-bar bg-danger" style="width: {{ $superPercent }}%"></div>
                                            </div>
                                            <span class="badge badge-secondary">
                                                {{ number_format($superCurrent, 0, ',', ' ') }} L
                                            </span>
                                            <span class="badge badge-{{ $superPercent < 20 ? 'danger' : ($superPercent < 50 ? 'warning' : 'success') }} ml-2">
                                                {{ $superPercent }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Gazole actuel:</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 10px;">
                                                <div class="progress-bar bg-warning" style="width: {{ $gazolePercent }}%"></div>
                                            </div>
                                            <span class="badge badge-secondary">
                                                {{ number_format($gazoleCurrent, 0, ',', ' ') }} L
                                            </span>
                                            <span class="badge badge-{{ $gazolePercent < 20 ? 'danger' : ($gazolePercent < 50 ? 'warning' : 'success') }} ml-2">
                                                {{ $gazolePercent }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                
                                @if($superCurrent < $superCapacity * 0.2 || $gazoleCurrent < $gazoleCapacity * 0.2)
                                <tr>
                                    <td colspan="2" class="pt-3">
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <strong>Attention :</strong> Niveau de stock critique !
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    <!-- Détails des cuves avec jaugeage -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5><i class="fas fa-oil-can mr-2"></i> Détail des cuves et jaugeages</h5>
                            
                            @php
                                $allTanks = $station->tanks()->orderBy('fuel_type')->orderBy('number')->get();
                            @endphp
                            
                            @if($allTanks->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>N° Cuve</th>
                                                <th>Carburant</th>
                                                <th>Capacité (L)</th>
                                                <th>Stock actuel (L)</th>
                                                <th>Remplissage</th>
                                                <th>Dernier jaugeage</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($allTanks as $tank)
                                                @php
                                                    $fillPercent = $tank->capacity > 0 ? ($tank->current_volume / $tank->capacity) * 100 : 0;
                                                    $lastGauging = $tank->latestGauging;
                                                    $lastGaugingDate = $lastGauging ? $lastGauging->measurement_date : null;
                                                    $statusClass = $fillPercent < 20 ? 'danger' : ($fillPercent < 50 ? 'warning' : 'success');
                                                    $statusText = $fillPercent < 20 ? 'Critique' : ($fillPercent < 50 ? 'Faible' : 'Normal');
                                                @endphp
                                                <tr>
                                                    <td class="font-weight-bold">{{ $tank->number }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $tank->fuel_type == 'super' ? 'info' : 'secondary' }}">
                                                            {{ strtoupper($tank->fuel_type) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ number_format($tank->capacity, 0, ',', ' ') }}</td>
                                                    <td class="font-weight-bold">{{ number_format($tank->current_volume, 0, ',', ' ') }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 mr-2" style="height: 6px;">
                                                                <div class="progress-bar bg-{{ $statusClass }}" style="width: {{ $fillPercent }}%"></div>
                                                            </div>
                                                            <span class="small">{{ round($fillPercent, 1) }}%</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($lastGaugingDate)
                                                            <i class="fas fa-calendar-alt mr-1"></i>
                                                            {{ \Carbon\Carbon::parse($lastGaugingDate)->format('d/m/Y H:i') }}
                                                            @if($lastGauging)
                                                                <br>
                                                                <small class="text-muted">
                                                                    Diff: {{ $lastGauging->difference_percentage ? number_format($lastGauging->difference_percentage, 1) . '%' : 'N/A' }}
                                                                </small>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">Jamais jaugé</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $statusClass }}">
                                                            {{ $statusText }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Aucune cuve configurée pour cette station.
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Manager assigné -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5><i class="fas fa-user-tie mr-2"></i> Manager assigné</h5>
                            @if($station->manager)
                                <div class="card card-outline card-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                @if($station->manager->photo && Storage::disk('public')->exists($station->manager->photo))
                                                    <img src="{{ Storage::url($station->manager->photo) }}" 
                                                         class="img-circle elevation-2" 
                                                         alt="Photo manager" 
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    <div class="img-circle elevation-2 bg-info d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-user fa-2x text-white"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">{{ $station->manager->name }}</h5>
                                                <p class="mb-1">
                                                    <i class="fas fa-envelope mr-1"></i> {{ $station->manager->email }}
                                                    <br>
                                                    <i class="fas fa-phone mr-1"></i> {{ $station->manager->phone ?? 'Non spécifié' }}
                                                </p>
                                                <small class="text-muted">
                                                    Assigné depuis: {{ $station->manager->created_at->format('d/m/Y') }}
                                                </small>
                                            </div>
                                            <div class="text-right">
                                                <a href="mailto:{{ $station->manager->email }}" 
                                                   class="btn btn-sm btn-outline-info" title="Envoyer email">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Aucun manager assigné à cette station
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Pompes et équipements -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5><i class="fas fa-gas-pump mr-2"></i> Pompes de la station</h5>
                            @php
                                $pumps = \App\Models\ShiftPompeDetail::whereHas('shiftSaisie', function($q) use ($station) {
                                    $q->where('station_id', $station->id);
                                })
                                ->select('pompe_nom', 'carburant')
                                ->distinct()
                                ->get()
                                ->groupBy('carburant');
                            @endphp
                            
                            @if($pumps->count() > 0)
                                <div class="row">
                                    @foreach($pumps as $fuelType => $fuelPumps)
                                        <div class="col-md-6">
                                            <div class="card card-outline card-secondary">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">
                                                        <i class="fas fa-{{ $fuelType == 'super' ? 'bolt' : ($fuelType == 'gazole' ? 'oil-can' : 'gas-pump') }} mr-2"></i>
                                                        {{ strtoupper($fuelType) }}
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        @foreach($fuelPumps as $pump)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                {{ $pump->pompe_nom }}
                                                                <span class="badge badge-primary badge-pill">
                                                                    {{ strtoupper($pump->carburant) }}
                                                                </span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Aucune pompe enregistrée pour cette station
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="text-right">
                        <a href="{{ route('chief.stations') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
                        </a>
                        <a href="{{ route('chief.rapports.stations', ['station_id' => $station->id]) }}" class="btn btn-primary ml-2">
                            <i class="fas fa-chart-bar mr-1"></i> Voir rapports
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Colonne latérale avec actions et statistiques -->
        <div class="col-md-4">
            <!-- Actions rapides -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i> Actions rapides
                    </h3>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('chief.dashboard', ['station_id' => $station->id]) }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt text-primary mr-2"></i> Dashboard Station
                        </a>
                        <a href="{{ route('chief.rapports.stations', ['station_id' => $station->id]) }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar text-success mr-2"></i> Rapports détaillés
                        </a>
                        <a href="{{ route('chief.validations', ['station' => $station->id]) }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-clipboard-check text-warning mr-2"></i> Validations en attente
                        </a>
                        @if($station->manager)
                        <a href="mailto:{{ $station->manager->email }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope text-info mr-2"></i> Contacter le manager
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Statistiques rapides -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i> Statistiques du mois
                    </h3>
                </div>
                <div class="card-body">
                    @if(isset($monthStats))
                    <div class="small-box bg-gradient-success mb-3">
                        <div class="inner">
                            <h3>{{ number_format($monthStats['total_sales'] ?? 0, 0, ',', ' ') }} F</h3>
                            <p>Ventes ce mois</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>Nombre shifts:</th>
                            <td class="text-right font-weight-bold">{{ $monthStats['shifts_count'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <th>Volume vendu:</th>
                            <td class="text-right font-weight-bold">{{ number_format($monthStats['total_litres'] ?? 0, 0, ',', ' ') }} L</td>
                        </tr>
                        <tr>
                            <th>Écart moyen:</th>
                            <td class="text-right font-weight-bold">
                                <span class="badge badge-{{ ($monthStats['avg_ecart'] ?? 0) > 0 ? 'success' : (($monthStats['avg_ecart'] ?? 0) < 0 ? 'danger' : 'secondary') }}">
                                    {{ number_format($monthStats['avg_ecart'] ?? 0, 0, ',', ' ') }} F
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Dépenses totales:</th>
                            <td class="text-right font-weight-bold">{{ number_format($monthStats['total_depenses'] ?? 0, 0, ',', ' ') }} F</td>
                        </tr>
                    </table>
                    @endif
                </div>
            </div>
            
            <!-- Derniers shifts -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i> Derniers shifts
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if(isset($recentShifts) && $recentShifts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Shift</th>
                                        <th>Ventes</th>
                                        <th>Statut</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentShifts as $shift)
                                    @php
                                        $statusBadge = [
                                            'en_attente' => ['class' => 'warning', 'icon' => 'clock'],
                                            'valide' => ['class' => 'success', 'icon' => 'check'],
                                            'rejete' => ['class' => 'danger', 'icon' => 'times'],
                                        ][$shift->statut] ?? ['class' => 'secondary', 'icon' => 'question'];
                                    @endphp
                                    <tr>
                                        <td>{{ $shift->date_shift->format('d/m') }}</td>
                                        <td>{{ $shift->shift }}</td>
                                        <td class="font-weight-bold">{{ number_format($shift->total_ventes, 0, ',', ' ') }} F</td>
                                        <td>
                                            <span class="badge badge-{{ $statusBadge['class'] }}">
                                                <i class="fas fa-{{ $statusBadge['icon'] }}"></i>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('chief.validation.show', $shift->id) }}" 
                                               class="btn btn-xs btn-outline-info" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Aucun shift récent</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('chief.rapports.stations', ['station_id' => $station->id]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye mr-1"></i> Voir tous les shifts
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Styles pour uniformiser les badges */
    .stats-card {
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    
    .stats-icon {
        font-size: 70px;
        opacity: 0.2;
        position: absolute;
        right: 10px;
        bottom: 10px;
    }
    
    .small-box .inner {
        padding: 12px 15px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .small-box .inner h3 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 5px;
        line-height: 1.1;
    }
    
    .small-box .inner p {
        font-size: 0.85rem;
        margin-bottom: 0;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .small-box-footer {
        padding: 8px 12px;
        background-color: rgba(0, 0, 0, 0.15);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        font-weight: 600;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    
    .small-box-footer:hover {
        background-color: rgba(0, 0, 0, 0.25);
        text-decoration: none;
    }
    
    .small-box-footer i {
        transition: transform 0.2s;
    }
    
    .small-box-footer:hover i {
        transform: translateX(3px);
    }
    
    .bg-purple {
        background-color: #6f42c1 !important;
        color: white;
    }
    
    .bg-opacity-25 {
        background-color: rgba(0, 0, 0, 0.2) !important;
    }
    
    .text-white-50 {
        color: rgba(255, 255, 255, 0.85) !important;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .img-circle {
        border-radius: 50%;
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
        transition: all 0.2s;
    }
    
    .list-group-item:hover {
        background-color: rgba(0,0,0,0.05);
        transform: translateX(5px);
    }
    
    .list-group-item:first-child {
        border-top: none;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .table-borderless td, .table-borderless th {
        border: none;
    }
    
    .bg-gradient-success {
        background: linear-gradient(45deg, #28a745, #20c997) !important;
        color: white;
    }
    
    .card {
        transition: box-shadow 0.3s;
    }
    
    .card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .stats-card {
        animation: fadeInUp 0.5s ease-out;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-card {
            height: 130px;
        }
        
        .stats-icon {
            font-size: 50px;
        }
        
        .small-box .inner h3 {
            font-size: 1.6rem;
        }
        
        .small-box .inner p {
            font-size: 0.7rem;
        }
        
        .small-box-footer {
            font-size: 0.7rem;
            padding: 6px 10px;
        }
        
        .text-white-50 {
            font-size: 0.6rem;
        }
    }
</style>
@endpush
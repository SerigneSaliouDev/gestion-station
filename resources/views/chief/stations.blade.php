@extends('layouts.chief')

@section('title', 'Gestion des Stations')
@section('page-icon', 'fa-gas-pump')
@section('page-title', 'Gestion des Stations')
@section('page-subtitle', 'Liste et gestion des stations de carburant')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('chief.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Stations</li>
@endsection

@section('stats')
@if(isset($stats) && is_array($stats))
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['total_ventes'] ?? 0, 0, ',', ' ') }} FCFA</h3>
                <p>Ventes totales</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['stations_actives'] ?? 0, 0, ',', ' ') }}</h3>
                <p>Stations actives</p>
            </div>
            <div class="icon">
                <i class="fas fa-gas-pump"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($stats['managers_actifs'] ?? 0, 0, ',', ' ') }}</h3>
                <p>Managers actifs</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($stats['total_capacite'] ?? 0, 0, ',', ' ') }} L</h3>
                <p>Capacité totale</p>
            </div>
            <div class="icon">
                <i class="fas fa-oil-can"></i>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Liste des stations</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 150px;">
                        <input type="text" name="table_search" class="form-control float-right" placeholder="Rechercher...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Code</th>
                            <th>Ville</th>
                            <th>Manager</th>
                            <th>Statut</th>
                            <th>Capacité (L)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stations as $station)
                        @php
                            $super = $station->capacite_super ?? 0;
                            $gazole = $station->capacite_gazole ?? 0;
                            $totalCapacity = $super + $gazole;
                        @endphp
                        <tr>
                            <td>{{ $station->id }}</td>
                            <td>
                                <strong>{{ $station->nom }}</strong><br>
                                <small class="text-muted">{{ $station->adresse }}</small>
                            </td>
                            <td>{{ $station->code }}</td>
                            <td>{{ $station->ville }}</td>
                            <td>
                                @if($station->manager)
                                    <span class="badge badge-info">{{ $station->manager->name }}</span>
                                @else
                                    <span class="badge badge-warning">Non assigné</span>
                                @endif
                            </td>
                            <td>
                                @if($station->statut == 'actif')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-center">
                                    <div class="font-weight-bold">{{ number_format($totalCapacity, 0, ',', ' ') }}</div>
                                    <small class="text-muted">
                                        Super: {{ number_format($super, 0, ',', ' ') }}<br>
                                        Gazole: {{ number_format($gazole, 0, ',', ' ') }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('chief.stations.show', $station->id) }}" 
                                       class="btn btn-info" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('chief.rapports.stations', ['station_id' => $station->id]) }}" 
                                       class="btn btn-primary" title="Rapports">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-gas-pump fa-2x mb-3"></i><br>
                                Aucune station enregistrée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($stations) && method_exists($stations, 'hasPages') && $stations->hasPages())
            <div class="card-footer clearfix">
                {{ $stations->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Cartes de résumé -->
<div class="row mt-3">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-info-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Stations par statut</span>
                <div class="progress-group">
                    <span class="float-left">Actives</span>
                    <span class="float-right font-weight-bold">{{ $stats['stations_actives'] ?? 0 }}</span>
                    <div class="progress progress-sm">
                        @php
                            $activePercentage = isset($stats['stations_actives']) && isset($stats['stations_total']) && $stats['stations_total'] > 0 
                                ? ($stats['stations_actives'] / $stats['stations_total']) * 100 
                                : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ $activePercentage }}%"></div>
                    </div>
                </div>
                <div class="progress-group">
                    <span class="float-left">Inactives</span>
                    <span class="float-right font-weight-bold">{{ $stats['stations_inactives'] ?? 0 }}</span>
                    <div class="progress progress-sm">
                        @php
                            $inactivePercentage = isset($stats['stations_inactives']) && isset($stats['stations_total']) && $stats['stations_total'] > 0 
                                ? ($stats['stations_inactives'] / $stats['stations_total']) * 100 
                                : 0;
                        @endphp
                        <div class="progress-bar bg-danger" style="width: {{ $inactivePercentage }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-chart-pie"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Répartition par ville</span>
                @php
                    $stationsByCity = isset($stations) ? $stations->groupBy('ville')->map->count() : collect();
                @endphp
                @forelse($stationsByCity->take(3) as $city => $count)
                <div class="progress-group">
                    <span class="float-left">{{ $city ?: 'Non spécifiée' }}</span>
                    <span class="float-right font-weight-bold">{{ $count }}</span>
                    <div class="progress progress-sm">
                        @php
                            $percentage = isset($stations) && $stations->count() > 0 ? ($count / $stations->count()) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-info" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-2">
                    <small>Aucune donnée de ville disponible</small>
                </div>
                @endforelse
                @if($stationsByCity->count() > 3)
                <div class="text-center mt-2">
                    <small class="text-muted">+ {{ $stationsByCity->count() - 3 }} autres villes</small>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-percentage"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Performances globales</span>
                <div class="d-flex justify-content-between">
                    <div class="text-center">
                        <h4 class="mb-0">{{ number_format($stats['moyenne_station'] ?? 0, 0, ',', ' ') }}</h4>
                        <small>Moyenne/station</small>
                    </div>
                    <div class="text-center">
                        <h4 class="mb-0">{{ isset($stations) ? $stations->count() : 0 }}</h4>
                        <small>Total stations</small>
                    </div>
                    <div class="text-center">
                        <h4 class="mb-0">{{ $stats['managers_actifs'] ?? 0 }}</h4>
                        <small>Managers</small>
                    </div>
                </div>
                @if(isset($stats['best_station']) && $stats['best_station'])
                <div class="mt-2 text-center">
                    <small class="text-muted">
                        <i class="fas fa-trophy text-warning"></i>
                        Meilleure station : {{ $stats['best_station']['nom'] ?? 'N/A' }}
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    
    .info-box-icon {
        font-size: 28px;
    }
    
    .info-box-content {
        padding: 15px;
    }
    
    .progress-group {
        margin-bottom: 10px;
    }
    
    .progress-group:last-child {
        margin-bottom: 0;
    }
    
    .progress-group span.float-left {
        font-size: 0.85rem;
    }
    
    .progress-group span.float-right {
        font-size: 0.85rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,0.03);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Recherche dans le tableau
    $('input[name="table_search"]').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Confirmation de suppression
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette station ?')) {
            e.preventDefault();
        }
    });
    
    // Gestion du bouton de recherche
    $('.btn-default').on('click', function() {
        $('input[name="table_search"]').trigger('keyup');
    });
    
    // Auto-focus sur le champ de recherche
    $('input[name="table_search"]').focus();
});
</script>
@endpush
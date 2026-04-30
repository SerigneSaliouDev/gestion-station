@extends('layouts.admin')

@section('title', 'Détails de la Station')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">{{ $station->nom }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.stations.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.stations.edit', $station->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Modifier
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informations de la station -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Informations générales
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Code:</th>
                                    <td><code>{{ $station->code }}</code></td>
                                </tr>
                                <tr>
                                    <th>Adresse:</th>
                                    <td>{{ $station->adresse }}</td>
                                </tr>
                                <tr>
                                    <th>Ville:</th>
                                    <td>{{ $station->ville }}</td>
                                </tr>
                                <tr>
                                    <th>Téléphone:</th>
                                    <td>{{ $station->telephone ?? 'Non renseigné' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Statut:</th>
                                    <td>
                                        @if($station->statut == 'actif')
                                            <span class="badge bg-success">Actif</span>
                                        @elseif($station->statut == 'inactif')
                                            <span class="badge bg-danger">Inactif</span>
                                        @else
                                            <span class="badge bg-warning">{{ $station->statut }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Manager:</th>
                                    <td>
                                        @if($station->manager)
                                            <a href="{{ route('admin.users.show', $station->manager->id) }}">
                                                {{ $station->manager->name }}
                                            </a>
                                        @else
                                            <span class="badge bg-warning">Non assigné</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Créée le:</th>
                                    <td>{{ $station->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière mise à jour:</th>
                                    <td>{{ $station->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Capacités -->
                    <div class="mt-4">
                        <h6 class="mb-3 text-primary">
                            <i class="fas fa-oil-can"></i> Capacités de stockage
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light border-primary">
                                    <div class="card-body text-center">
                                        <div class="text-muted small">Super/Essence</div>
                                        <div class="h4 text-primary">{{ number_format($station->capacite_super ?? 0) }} L</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light border-info">
                                    <div class="card-body text-center">
                                        <div class="text-muted small">Gazole</div>
                                        <div class="h4 text-info">{{ number_format($station->capacite_gazole ?? 0) }} L</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light border-success">
                                    <div class="card-body text-center">
                                        <div class="text-muted small">Essence Pirogue</div>
                                        <div class="h4 text-success">{{ number_format($station->capacite_essence_pirogue ?? 0) }} L</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques du mois -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Statistiques du mois ({{ now()->format('F') }})
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Shifts validés</span>
                            <span class="badge bg-primary rounded-pill">{{ $monthStats['shifts_count'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Ventes totales</span>
                            <span class="text-success fw-bold">
                                {{ number_format($monthStats['total_sales'] ?? 0) }} FCFA
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Litres vendus</span>
                            <span class="text-info fw-bold">
                                {{ number_format($monthStats['total_litres'] ?? 0) }} L
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Dépenses totales</span>
                            <span class="text-danger">
                                {{ number_format($monthStats['total_depenses'] ?? 0) }} FCFA
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Écart moyen</span>
                            <span class="badge 
                                @if(($monthStats['avg_ecart'] ?? 0) >= 0) bg-success @else bg-danger @endif">
                                {{ number_format($monthStats['avg_ecart'] ?? 0) }} FCFA
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Vente moyenne/shift</span>
                            <span class="fw-bold">
                                {{ number_format($monthStats['avg_sales_per_shift'] ?? 0) }} FCFA
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Stocks actuels -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-database"></i> Stocks actuels
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Super/Essence</span>
                            <span class="fw-bold">{{ number_format($stocks['super'] ?? 0) }} L</span>
                        </div>
                        @php
                            $superPercent = $station->capacite_super > 0 
                                ? (($stocks['super'] ?? 0) / $station->capacite_super) * 100 
                                : 0;
                        @endphp
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ min($superPercent, 100) }}%">
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ number_format($stocks['super'] ?? 0) }} / {{ number_format($station->capacite_super ?? 0) }} L
                        </small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Gazole</span>
                            <span class="fw-bold">{{ number_format($stocks['gasoil'] ?? 0) }} L</span>
                        </div>
                        @php
                            $gasoilPercent = $station->capacite_gazole > 0 
                                ? (($stocks['gasoil'] ?? 0) / $station->capacite_gazole) * 100 
                                : 0;
                        @endphp
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ min($gasoilPercent, 100) }}%">
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ number_format($stocks['gasoil'] ?? 0) }} / {{ number_format($station->capacite_gazole ?? 0) }} L
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Derniers shifts -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Derniers shifts
                    </h6>
                    <a href="{{ route('admin.shifts.index', ['station_id' => $station->id]) }}" 
                       class="btn btn-sm btn-outline-primary">
                        Voir tous les shifts
                    </a>
                </div>
                <div class="card-body">
                    @if($recentShifts && $recentShifts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Shift</th>
                                        <th>Agent</th>
                                        <th>Ventes</th>
                                        <th>Dépenses</th>
                                        <th>Écart</th>
                                        <th>Statut</th>
                                       
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentShifts as $shiftItem)
                                    <tr>
                                        <td>{{ $shiftItem->date_shift->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($shiftItem->shift == 'matin') bg-info
                                                @elseif($shiftItem->shift == 'soir') bg-warning
                                                @else bg-secondary @endif">
                                                {{ ucfirst($shiftItem->shift) }}
                                            </span>
                                        </td>
                                        <td>{{ $shiftItem->user->name ?? 'Non spécifié' }}</td>
                                        <td class="text-success">
                                            {{ number_format($shiftItem->total_ventes) }} FCFA
                                        </td>
                                        <td class="text-danger">
                                            {{ number_format($shiftItem->total_depenses) }} FCFA
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($shiftItem->ecart_final >= 0) bg-success @else bg-danger @endif">
                                                {{ number_format($shiftItem->ecart_final) }} FCFA
                                            </span>
                                        </td>
                                        <td>
                                            @if($shiftItem->statut == 'valide')
                                                <span class="badge bg-success">Validé</span>
                                            @elseif($shiftItem->statut == 'en_attente')
                                                <span class="badge bg-warning">En attente</span>
                                            @else
                                                <span class="badge bg-danger">Rejeté</span>
                                            @endif
                                        </td>
                                       
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">Aucun shift enregistré pour cette station</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    
    <!-- Gestion du manager -->
   
            

        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($sales30Days && $sales30Days->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = @json($sales30Days->map(function($item) {
        return $item->sales ?? 0;
    }));
    
    const labels = @json($sales30Days->map(function($item) {
        return \Carbon\Carbon::parse($item->date)->format('d/m');
    }));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventes (FCFA)',
                data: salesData,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' FCFA';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection

@section('styles')
<style>
    .progress {
        background-color: #e9ecef;
    }
    .card.border-primary {
        border-left: 4px solid #4e73df !important;
    }
    .card.border-info {
        border-left: 4px solid #36b9cc !important;
    }
    .card.border-success {
        border-left: 4px solid #1cc88a !important;
    }
</style>
@endsection
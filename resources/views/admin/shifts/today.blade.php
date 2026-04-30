@extends('layouts.admin')

@section('title', 'Shifts du Jour')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Shifts du {{ $today->format('d/m/Y') }}</h1>
        <div>
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> Tous les shifts
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Shifts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_shifts'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Validés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['validated_shifts'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['pending_shifts'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Ventes totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_sales']) }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtres
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.shifts.today') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="valide" {{ request('status') == 'valide' ? 'selected' : '' }}>Validés</option>
                        <option value="en_attente" {{ request('status') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="rejete" {{ request('status') == 'rejete' ? 'selected' : '' }}>Rejetés</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Station</label>
                    <select name="station_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Toutes les stations</option>
                        @foreach(\App\Models\Station::all() as $station)
                            <option value="{{ $station->id }}" {{ request('station_id') == $station->id ? 'selected' : '' }}>
                                {{ $station->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" 
                           value="{{ request('date', $today->format('Y-m-d')) }}"
                           onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des shifts -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Liste des shifts
            </h6>
            <div>
                <span class="badge bg-info">{{ $shifts->total() }} résultats</span>
            </div>
        </div>
        <div class="card-body">
            @if($shifts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Station</th>
                                <th>Date/Shift</th>
                                <th>Agent</th>
                                <th>Ventes</th>
                                <th>Litres</th>
                                <th>Dépenses</th>
                                <th>Écart</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shifts as $shift)
                            <tr>
                                <td>
                                    <strong>#{{ $shift->id }}</strong>
                                    <div class="small text-muted">
                                        {{ $shift->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.stations.show', $shift->station_id) }}">
                                        {{ $shift->station->nom }}
                                    </a>
                                    <div class="small text-muted">
                                        {{ $shift->station->code }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $shift->date_shift->format('d/m/Y') }}</span>
                                        <span class="badge 
                                            @if($shift->shift == 'matin') bg-info
                                            @elseif($shift->shift == 'soir') bg-warning
                                            @else bg-secondary @endif">
                                            {{ ucfirst($shift->shift) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    {{ $shift->user->name ?? 'Non spécifié' }}
                                    <div class="small text-muted">
                                        {{ $shift->user->role ?? '' }}
                                    </div>
                                </td>
                                <td class="text-success fw-bold">
                                    {{ number_format($shift->total_ventes) }} FCFA
                                </td>
                                <td>
                                    {{ number_format($shift->total_litres) }} L
                                </td>
                                <td class="text-danger">
                                    {{ number_format($shift->total_depenses) }} FCFA
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($shift->ecart_final >= 0) bg-success
                                        @else bg-danger @endif">
                                        {{ number_format($shift->ecart_final) }} FCFA
                                    </span>
                                </td>
                                <td>
                                    @if($shift->statut == 'valide')
                                        <span class="badge bg-success">Validé</span>
                                        @if($shift->valide_le)
                                            <div class="small text-muted">
                                                {{ $shift->valide_le->format('H:i') }}
                                            </div>
                                        @endif
                                    @elseif($shift->statut == 'en_attente')
                                        <span class="badge bg-warning">En attente</span>
                                    @elseif($shift->statut == 'rejete')
                                        <span class="badge bg-danger">Rejeté</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.shifts.show', $shift->id) }}" 
                                           class="btn btn-outline-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.shifts.edit', $shift->id) }}" 
                                           class="btn btn-outline-primary" title="Éditer">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($shift->statut == 'en_attente')
                                            <button type="button" class="btn btn-outline-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#validateModal{{ $shift->id }}"
                                                    title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasRole('admin'))
                                            <form action="{{ route('admin.shifts.destroy', $shift->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" 
                                                        onclick="return confirm('Supprimer ce shift?')"
                                                        title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal de validation pour chaque shift -->
                            @if($shift->statut == 'en_attente')
                            <div class="modal fade" id="validateModal{{ $shift->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.validations.validate', $shift->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Valider le Shift #{{ $shift->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <strong>Informations du shift:</strong><br>
                                                    Station: {{ $shift->station->nom }}<br>
                                                    Date: {{ $shift->date_shift->format('d/m/Y') }}<br>
                                                    Ventes: {{ number_format($shift->total_ventes) }} FCFA<br>
                                                    Écart: {{ number_format($shift->ecart_final) }} FCFA
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Commentaire (optionnel)</label>
                                                    <textarea name="comment" class="form-control" rows="3" 
                                                              placeholder="Ajouter un commentaire de validation..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-success">Valider le shift</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Totaux:</th>
                                <th class="text-success">
                                    {{ number_format($shifts->where('statut', 'valide')->sum('total_ventes')) }} FCFA
                                </th>
                                <th>
                                    {{ number_format($shifts->where('statut', 'valide')->sum('total_litres')) }} L
                                </th>
                                <th class="text-danger">
                                    {{ number_format($shifts->where('statut', 'valide')->sum('total_depenses')) }} FCFA
                                </th>
                                <th>
                                    {{ number_format($shifts->where('statut', 'valide')->sum('ecart_final')) }} FCFA
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Affichage de {{ $shifts->firstItem() }} à {{ $shifts->lastItem() }} sur {{ $shifts->total() }} shifts
                    </div>
                    <nav>
                        {{ $shifts->withQueryString()->links() }}
                    </nav>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">Aucun shift pour aujourd'hui</h5>
                    <p class="text-muted mb-4">
                        Aucun shift n'a été enregistré pour la date du {{ $today->format('d/m/Y') }}
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Retour au dashboard
                        </a>
                        <a href="{{ route('admin.shifts.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> Voir tous les shifts
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card shadow border-start-success">
                <div class="card-body">
                    <h6 class="card-title text-success">
                        <i class="fas fa-bolt"></i> Actions rapides
                    </h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.validations.pending') }}" class="btn btn-warning">
                            <i class="fas fa-clock"></i> Voir les validations en attente
                        </a>
                        <a href="{{ route('admin.reports.daily') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Rapport journalier
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Répartition par statut
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="statusChart" height="100"></canvas>
                        </div>
                        <div class="col-md-4">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Validés
                                    <span class="badge bg-success rounded-pill">{{ $stats['validated_shifts'] }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    En attente
                                    <span class="badge bg-warning rounded-pill">{{ $stats['pending_shifts'] }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Rejetés
                                    <span class="badge bg-danger rounded-pill">
                                        {{ $stats['total_shifts'] - $stats['validated_shifts'] - $stats['pending_shifts'] }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js pour la répartition des statuts
    const ctx = document.getElementById('statusChart').getContext('2d');
    const validated = {{ $stats['validated_shifts'] }};
    const pending = {{ $stats['pending_shifts'] }};
    const rejected = {{ $stats['total_shifts'] - $stats['validated_shifts'] - $stats['pending_shifts'] }};
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Validés', 'En attente', 'Rejetés'],
            datasets: [{
                data: [validated, pending, rejected],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Auto-refresh toutes les 30 secondes
    setTimeout(() => {
        window.location.reload();
    }, 30000);
});
</script>
@endsection

@section('styles')
<style>
    .table th {
        background-color: #f8f9fc;
        font-weight: 600;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .card.border-start-success {
        border-left: 4px solid #28a745 !important;
    }
</style>
@endsection
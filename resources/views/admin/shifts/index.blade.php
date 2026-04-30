@extends('layouts.admin')

@section('title', 'Gestion des Shifts')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt text-primary"></i> Gestion des Shifts
        </h1>
       
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Station</label>
                    <select name="station_id" class="form-control">
                        <option value="">Toutes les stations</option>
                        @foreach($stations as $station)
                        <option value="{{ $station->id }}" {{ request('station_id') == $station->id ? 'selected' : '' }}>
                            {{ $station->nom }} ({{ $station->code }})
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-control">
                        <option value="">Tous</option>
                        <option value="valide" {{ request('status') == 'valide' ? 'selected' : '' }}>Validé</option>
                        <option value="en_attente" {{ request('status') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="rejete" {{ request('status') == 'rejete' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Utilisateur</label>
                    <select name="user_id" class="form-control">
                        <option value="">Tous</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Date de début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
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
                                {{ $stats['total'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                                {{ $stats['validated'] }}
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
                                {{ $stats['pending'] }}
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
                                Ventes Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_sales'], 0, ',', ' ') }} FCFA
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

    <!-- Tableau des shifts -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Liste des Shifts ({{ $shifts->total() }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Date & Shift</th>
                            <th>Station</th>
                            <th>Utilisateur</th>
                            <th>Ventes</th>
                            <th>Écart</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                        <tr>
                            <td>{{ $shift->id }}</td>
                            <td>
                                <strong>{{ $shift->date_shift->format('d/m/Y') }}</strong>
                                <br>
                                <small class="text-muted">Shift: {{ $shift->shift }}</small>
                            </td>
                            <td>
                                @if($shift->station)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-gas-pump text-primary mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold">{{ $shift->station->nom }}</div>
                                        <div class="small text-muted">{{ $shift->station->code }}</div>
                                    </div>
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($shift->user)
                                <div class="d-flex align-items-center">
                                    <div class="mr-2">
                                        <div class="icon-circle bg-primary" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                            <span class="text-white">{{ substr($shift->user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">{{ $shift->user->name }}</div>
                                        <div class="small text-muted">{{ $shift->user->email }}</div>
                                    </div>
                                </div>
                                @endif
                            </td>
                            <td class="font-weight-bold text-success">
                                {{ number_format($shift->total_ventes, 0, ',', ' ') }} FCFA
                                <br>
                                <small class="text-muted">{{ number_format($shift->total_litres, 0, ',', ' ') }} L</small>
                            </td>
                            <td class="{{ $shift->ecart_final < 0 ? 'text-danger' : 'text-success' }}">
                                <strong>{{ number_format($shift->ecart_final, 0, ',', ' ') }} FCFA</strong>
                            </td>
                            <td>
                                @if($shift->statut == 'valide')
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Validé
                                </span>
                                @elseif($shift->statut == 'en_attente')
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock"></i> En attente
                                </span>
                                @else
                                <span class="badge badge-danger">
                                    <i class="fas fa-times"></i> Rejeté
                                </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                 
                                    <a href="{{ route('admin.shifts.edit', $shift) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Éditer">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->user()->hasRole('administrateur'))
                                    <form action="{{ route('admin.shifts.destroy', $shift) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer ce shift?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                    <h5>Aucun shift trouvé</h5>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($shifts->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Affichage de {{ $shifts->firstItem() }} à {{ $shifts->lastItem() }} sur {{ $shifts->total() }} shifts
                </div>
                <div>
                    {{ $shifts->withQueryString()->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .icon-circle {
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style>
@endpush
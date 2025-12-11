@extends('layouts.app')

@section('title', 'Historique des Prix')
@section('page-title', 'Historique des Modifications de Prix')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.index_form') }}">Manager</a></li>
<li class="breadcrumb-item"><a href="{{ route('manager.edit_prices') }}">Prix</a></li>
<li class="breadcrumb-item active">Historique</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header odyssee-bg-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-history"></i> Historique Complet des Prix
        </h3>
        <div class="card-tools">
            <a href="{{ route('manager.edit_prices') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Retour aux prix
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filtres -->
        <div class="row mb-4">
            <div class="col-md-12">
                <form method="GET" action="{{ route('manager.price_history') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="fuel_type" class="mr-2">Carburant:</label>
                        <select name="fuel_type" id="fuel_type" class="form-control">
                            <option value="">Tous</option>
                            @foreach($fuelTypes as $type)
                                <option value="{{ $type }}" {{ request('fuel_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mr-3">
                        <label for="date_from" class="mr-2">Du:</label>
                        <input type="date" name="date_from" id="date_from" 
                               class="form-control" 
                               value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="form-group mr-3">
                        <label for="date_to" class="mr-2">Au:</label>
                        <input type="date" name="date_to" id="date_to" 
                               class="form-control" 
                               value="{{ request('date_to') }}">
                    </div>
                    
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                    
                    <a href="{{ route('manager.price_history') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                </form>
            </div>
        </div>
        
        <!-- Tableau historique -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th width="15%">Date/Heure</th>
                        <th width="15%">Carburant</th>
                        <th width="15%">Prix (F CFA/L)</th>
                        <th width="20%">Modifié par</th>
                        <th width="35%">Raison</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $record)
                    <tr>
                        <td>
                            <i class="far fa-clock text-muted mr-1"></i>
                            {{ $record->created_at->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $record->created_at->format('H:i:s') }}</small>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ ucfirst($record->fuel_type) }}</span>
                        </td>
                        <td class="font-weight-bold text-primary">
                            {{ number_format($record->price_per_liter, 0, ',', ' ') }}
                        </td>
                        <td>
                            <i class="fas fa-user text-muted mr-1"></i>
                            {{ $record->changer->name ?? 'N/A' }}
                        </td>
                        <td>
                            {{ $record->change_reason }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun historique de prix disponible</h5>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($history->hasPages())
        <div class="mt-3">
            {{ $history->links() }}
        </div>
        @endif
        
        <!-- Statistiques -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <div class="h2 text-primary">{{ $history->total() }}</div>
                        <div class="text-muted">Modifications totales</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <div class="h2 text-success">{{ $fuelTypes->count() }}</div>
                        <div class="text-muted">Types de carburant</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        @php
                            $firstRecord = $history->first();
                            $lastRecord = $history->last();
                        @endphp
                        <div class="h4 text-info">
                            @if($firstRecord && $lastRecord)
                                {{ $firstRecord->created_at->diffInDays($lastRecord->created_at) + 1 }} jours
                            @else
                                0 jour
                            @endif
                        </div>
                        <div class="text-muted">Période couverte</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
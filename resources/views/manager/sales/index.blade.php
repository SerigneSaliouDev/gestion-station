@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Historique des ventes</h2>
                <a href="{{ route('manager.sales.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle vente
                </a>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Type de carburant</label>
                    <select name="fuel_type" class="form-control">
                        <option value="">Tous</option>
                        @foreach($fuelTypes as $key => $name)
                            <option value="{{ $key }}" {{ request('fuel_type') == $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Mode de paiement</label>
                    <select name="payment_method" class="form-control">
                        <option value="">Tous</option>
                        @foreach($paymentMethods as $key => $name)
                            <option value="{{ $key }}" {{ request('payment_method') == $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Date début</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                    <a href="{{ route('manager.sales.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total ventes</h6>
                    <h2 class="mb-0">{{ number_format($stats['total_sales']) }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Quantité totale</h6>
                    <h2 class="mb-0">{{ number_format($stats['total_quantity'], 1) }} L</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Montant total</h6>
                    <h2 class="mb-0">{{ number_format($stats['total_amount'], 0, ',', ' ') }} F CFA</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Moyenne/vente</h6>
                    <h2 class="mb-0">{{ number_format($stats['avg_amount'], 0, ',', ' ') }} F CFA</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des ventes -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Carburant</th>
                            <th>Quantité (L)</th>
                            <th>Prix unitaire</th>
                            <th>Montant</th>
                            <th>Paiement</th>
                            <th>Client</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $fuelTypes[$sale->fuel_type] ?? $sale->fuel_type }}
                                    </span>
                                </td>
                                <td>{{ number_format($sale->quantity, 1) }}</td>
                                <td>{{ number_format($sale->unit_price, 0, ',', ' ') }}</td>
                                <td>
                                    <strong>{{ number_format($sale->total_amount, 0, ',', ' ') }}</strong> F CFA
                                </td>
                                <td>
                                    <span class="badge {{ $sale->payment_method == 'cash' ? 'bg-success' : 'bg-info' }}">
                                        {{ $paymentMethods[$sale->payment_method] ?? $sale->payment_method }}
                                    </span>
                                </td>
                                <td>
                                    @if($sale->customer_name)
                                        {{ $sale->customer_name }}
                                        @if($sale->vehicle_number)
                                            <br><small>{{ $sale->vehicle_number }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('manager.sales.show', $sale->id) }}" 
                                       class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(!$sale->cancelled_at)
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                title="Annuler" data-bs-toggle="modal" 
                                                data-bs-target="#cancelModal{{ $sale->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-secondary">Annulée</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Modal d'annulation -->
                            @if(!$sale->cancelled_at)
                                <div class="modal fade" id="cancelModal{{ $sale->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('manager.sales.cancel', $sale->id) }}" method="POST">
                                                @csrf
                                                @method('POST')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Annuler la vente</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Êtes-vous sûr de vouloir annuler cette vente ?</p>
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Cette action restaurera {{ number_format($sale->quantity, 1) }} L de 
                                                        {{ $fuelTypes[$sale->fuel_type] ?? $sale->fuel_type }} dans le stock.
                                                    </div>
                                                    <p>
                                                        <strong>Détails:</strong><br>
                                                        Date: {{ $sale->sale_date->format('d/m/Y H:i') }}<br>
                                                        Quantité: {{ number_format($sale->quantity, 1) }} L<br>
                                                        Montant: {{ number_format($sale->total_amount, 0, ',', ' ') }} F CFA
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        Non, garder
                                                    </button>
                                                    <button type="submit" class="btn btn-danger">
                                                        Oui, annuler
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-gas-pump fa-3x text-muted mb-3"></i>
                                        <h5>Aucune vente enregistrée</h5>
                                        <p class="text-muted">Commencez par enregistrer votre première vente.</p>
                                        <a href="{{ route('manager.sales.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Nouvelle vente
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($sales->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $sales->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
    
    <!-- Actions supplémentaires -->
    <div class="mt-4">
        <a href="{{ route('manager.sales.report') }}" class="btn btn-outline-primary">
            <i class="fas fa-chart-bar"></i> Rapport détaillé
        </a>
        <a href="{{ route('manager.stocks.dashboard') }}" class="btn btn-outline-info">
            <i class="fas fa-oil-can"></i> Tableau de bord stocks
        </a>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Historique des Mouvements de Stock')
@section('page-title', 'Historique Complet des Mouvements')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.stocks.dashboard') }}">Stocks</a></li>
<li class="breadcrumb-item active">Historique</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtres -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtres</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.stocks.history') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Type de Carburant</label>
                        <select name="fuel_type" class="form-control">
                            <option value="">Tous les carburants</option>
                            @foreach($fuelTypes as $key => $name)
                                <option value="{{ $key }}" {{ request('fuel_type') == $key ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Date de début</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Date de fin</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Récapitulatif -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-truck-loading"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Réceptions</span>
                    <span class="info-box-number">{{ number_format($totals['receptions'], 2, ',', ' ') }} L</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ventes</span>
                    <span class="info-box-number">{{ number_format($totals['sales'], 2, ',', ' ') }} L</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ajustements</span>
                    <span class="info-box-number">{{ number_format($totals['adjustments'], 2, ',', ' ') }} L</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tableau des mouvements -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mouvements de Stock</h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ $movements->total() }} mouvements</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Carburant</th>
                            <th>Quantité (L)</th>
                            <th>Prix Unitaire</th>
                            <th>Total</th>
                            <th>Stock Avant</th>
                            <th>Stock Après</th>
                            <th>Détails</th>
                            <th>Enregistré par</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                            @php
                                $isReception = $movement->movement_type === 'reception';
                                $isSale = $movement->movement_type === 'vente';
                                $isAdjustment = $movement->movement_type === 'ajustement';
                                
                                $typeClass = $isReception ? 'badge-success' : 
                                            ($isSale ? 'badge-danger' : 'badge-warning');
                                
                                $quantityClass = $movement->quantity > 0 ? 'text-success' : 
                                               ($movement->quantity < 0 ? 'text-danger' : 'text-muted');
                            @endphp
                            <tr>
                                <td>{{ $movement->movement_date->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge {{ $typeClass }}">
                                        {{ ucfirst($movement->movement_type) }}
                                        @if($movement->auto_generated)
                                            <i class="fas fa-robot ml-1"></i>
                                        @endif
                                    </span>
                                </td>
                                <td>{{ ucfirst($movement->fuel_type) }}</td>
                                <td class="{{ $quantityClass }} font-weight-bold">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 2, ',', ' ') }}
                                </td>
                                <td>
                                    @if($movement->unit_price > 0)
                                        {{ number_format($movement->unit_price, 0, ',', ' ') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($movement->total_amount != 0)
                                        {{ number_format($movement->total_amount, 0, ',', ' ') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ number_format($movement->stock_before, 2, ',', ' ') }}</td>
                                <td>{{ number_format($movement->stock_after, 2, ',', ' ') }}</td>
                                <td>
                                    @if($isReception)
                                        <small>
                                            <i class="fas fa-truck"></i> {{ $movement->supplier_name }}<br>
                                            BL: {{ $movement->invoice_number }}<br>
                                            Cuve: {{ $movement->tank_number }}
                                        </small>
                                    @elseif($isSale && $movement->shiftSaisie)
                                        <small>
                                            <i class="fas fa-cash-register"></i> Shift #{{ $movement->shiftSaisie->id }}<br>
                                            {{ $movement->shiftSaisie->responsable }}
                                        </small>
                                    @elseif($isAdjustment)
                                        <small class="text-muted">{{ Str::limit($movement->notes, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ optional($movement->recorder)->name }}</small><br>
                                    <small class="text-muted">{{ $movement->created_at->format('H:i') }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $movements->appends(request()->query())->links() }}
        </div>
    </div>
    
    <!-- Actions -->
    <div class="row mt-3">
        <div class="col-md-6">
            <a href="{{ route('manager.stocks.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('manager.stocks.balance') }}" class="btn btn-info">
                <i class="fas fa-chart-pie"></i> Voir le bilan détaillé
            </a>
        </div>
    </div>
</div>
@endsection
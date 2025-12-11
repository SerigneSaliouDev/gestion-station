@extends('layouts.app') 

@section('title', 'Tableau de Bord des Stocks')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Tableau de Bord des Stocks</h1>

    {{-- Affichage des messages flash (success/error/alert) --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (session('alert'))
        <div class="alert alert-warning">{{ session('alert') }}</div>
    @endif
    
    {{-- 1. SYNTHÈSE DU STOCK PAR CARBURANT --}}
    <h2 class="mt-4 mb-3">Synthèse par Carburant</h2>
    <div class="row">
        @foreach($stocks as $typeKey => $data)
            <div class="col-md-6 col-lg-3">
                <div class="card info-box">
                    <span class="info-box-icon bg-{{ $data['current'] < 5000 ? 'warning' : 'info' }}"><i class="fas fa-gas-pump"></i></span>
                    <div class="info-box-content">
                        {{-- Utilisation de ucfirst pour afficher 'Super' ou 'Gazole' --}}
                        <span class="info-box-text">{{ ucfirst($typeKey) }} (Stock Actuel)</span>
                        <span class="info-box-number">
                            {{ number_format($data['current'], 0, ',', ' ') }} L
                        </span>
                        <div class="small mt-1">
                            @php
                                $variation = round($data['variation'], 2);
                                $class = $variation > 0 ? 'text-success' : ($variation < 0 ? 'text-danger' : 'text-muted');
                                $icon = $variation > 0 ? 'fa-arrow-up' : ($variation < 0 ? 'fa-arrow-down' : 'fa-minus');
                            @endphp
                            <span class="{{ $class }}"><i class="fas {{ $icon }}"></i> {{ $variation }}% (Mois)</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 2. ALERTES ET JAUGEAGE RÉCENT --}}
    <div class="row mt-4">
        {{-- Section Alertes --}}
        <div class="col-lg-6">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Alertes et Risques</h3>
                </div>
                <div class="card-body">
                    @if(count($alerts) > 0)
                        @foreach($alerts as $alert)
                            <div class="alert alert-{{ $alert['severity'] == 'warning' ? 'warning' : 'danger' }} alert-dismissible">
                                <i class="icon fas fa-exclamation-circle"></i> 
                                <strong>{{ $alert['severity'] == 'warning' ? 'Alerte Stock Bas' : 'Écart Jaugeage' }}:</strong>
                                {{ $alert['message'] }}
                            </div>
                        @endforeach
                    @else
                        <p class="text-success"><i class="fas fa-check-circle"></i> Aucune alerte de stock ou de jaugeage critique.</p>
                    @endif
                </div>
                <div class="card-footer text-right">
                    <a href="#" class="btn btn-sm btn-outline-info">Voir tous les rapports</a>
                </div>
            </div>
        </div>

        {{-- Section Jaugeages Récents --}}
        <div class="col-lg-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Jaugeages Récents (Cuves)</h3>
                    <div class="card-tools">
                        <a href="{{ route('manager.stocks.tank-levels.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Nouveau Jaugeage
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Cuve</th>
                                <th>Niveau (cm)</th>
                                <th>Volume</th>
                                <th>Écart (%)</th>
                                <th>Mesuré le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestTankLevels as $level)
                                @php
                                    $diff = round($level->difference_percentage, 2);
                                    $diffClass = abs($diff) > 1.0 ? 'text-danger font-weight-bold' : 'text-success';
                                @endphp
                                <tr>
                                    <td>{{ $level->tank_number }} ({{ $level->fuel_type }})</td>
                                    <td>{{ number_format($level->level_cm, 1) }} cm</td>
                                    <td>{{ number_format($level->volume_liters, 0, ',', ' ') }} L</td>
                                    <td class="{{ $diffClass }}">
                                        {{ $diff > 0 ? '+' : '' }}{{ $diff }}%
                                    </td>
                                    <td>{{ optional($level->measurement_date)->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Aucun jaugeage récent trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. DERNIERS MOUVEMENTS DE STOCK --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> 10 Derniers Mouvements de Stock</h3>
                    <div class="card-tools">
                        <a href="{{ route('manager.stocks.receptions.create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-truck"></i> Enregistrer Réception
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date/Heure</th>
                                <th>Type</th>
                                <th>Carburant</th>
                                <th>Quantité (L)</th>
                                <th>Prix Unitaire</th>
                                <th>Stock Après</th>
                                <th>Enregistré par</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestMovements as $movement)
                                @php
                                    $isReception = $movement->movement_type === 'reception';
                                    $typeClass = $isReception ? 'badge bg-success' : 'badge bg-danger';
                                @endphp
                                <tr>
                                    <td>{{ optional($movement->movement_date)->format('d/m/Y') }}<br><small>{{ optional($movement->created_at)->format('H:i') }}</small></td>
                                    <td><span class="{{ $typeClass }}">{{ ucfirst($movement->movement_type) }}</span></td>
                                    <td>{{ ucfirst($movement->fuel_type) }}</td>
                                    <td class="{{ $isReception ? 'text-success' : 'text-danger' }}">{{ $isReception ? '+' : '' }}{{ number_format($movement->quantity, 0, ',', ' ') }}</td>
                                    <td>{{ number_format($movement->unit_price, 0, ',', ' ') }} F CFA</td>
                                    <td>{{ number_format($movement->stock_after, 0, ',', ' ') }} L</td>
                                    <td>{{ optional($movement->recorder)->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Aucun mouvement de stock enregistré récemment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Gestion des Tolérances de Jaugeage')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-balance-scale"></i> Normes de Tolérance par Type de Produit
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0">Produits Noirs (3‰)</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Gasoil
                                            <span class="badge badge-dark badge-pill">3‰</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Fioul domestique
                                            <span class="badge badge-dark badge-pill">3‰</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Fueloil
                                            <span class="badge badge-dark badge-pill">3‰</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Produits Blancs (5‰)</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Essence pirogue 
                                            <span class="badge badge-light badge-pill">5‰</span>
                                        </li>
                                     
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Super
                                            <span class="badge badge-light badge-pill">5‰</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Historique des écarts -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Historique des Écarts Significatifs</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Cuve</th>
                                        <th>Produit</th>
                                        <th>Écart</th>
                                        <th>Tolérance</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($discrepancies as $level)
                                    <tr>
                                        <td>{{ $level->measurement_date->format('d/m/Y H:i') }}</td>
                                        <td>{{ $level->tank_number }}</td>
                                        <td>{{ $level->fuel_type }}</td>
                                        <td class="{{ abs($level->difference_percentage) > 5 ? 'text-danger font-weight-bold' : 'text-warning' }}">
                                            {{ number_format($level->difference_percentage, 1) }}‰
                                        </td>
                                        <td>
                                            @if(in_array(strtolower($level->fuel_type), ['diesel', 'gasoil']))
                                                3‰
                                            @else
                                                5‰
                                            @endif
                                        </td>
                                        <td>
                                            @if(abs($level->difference_percentage) > 5)
                                                <span class="badge badge-danger">Critique</span>
                                            @else
                                                <span class="badge badge-warning">Surveillance</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
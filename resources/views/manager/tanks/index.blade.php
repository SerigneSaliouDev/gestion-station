@extends('layouts.app')

@section('title', 'Liste des cuves')
@section('page-title', 'Gestion des cuves')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.stocks.dashboard') }}">Tableau de bord</a></li>
<li class="breadcrumb-item active">Cuves</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #333, #555); color: white;">
                <h3 class="card-title">
                    <i class="fas fa-oil-can mr-2"></i>Liste des cuves
                </h3>
                <div class="card-tools">
                    <a href="{{ route('manager.tanks.create') }}" class="btn btn-sm" style="background-color: #FF7F00; color: white;">
                        <i class="fas fa-plus mr-1"></i> Nouvelle cuve
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                </div>
                @endif
                
                @if($tanks->isEmpty())
                <div class="alert alert-info">
                    <i class="icon fas fa-info-circle"></i>
                    Aucune cuve créée. 
                    <a href="{{ route('manager.tanks.create') }}" class="alert-link">
                        Créez votre première cuve
                    </a>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th>Numéro</th>
                                <th>Carburant</th>
                                <th>Capacité</th>
                                <th>Stock actuel</th>
                                <th>Remplissage</th>
                                <th>Description</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tanks as $tank)
                            <tr>
                                <td>
                                    <strong>{{ $tank->number }}</strong>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = [
                                            'super' => 'badge-danger',
                                            'gasoil' => 'badge-success',
                                            'Essence Pirogue' => 'badge-primary',
                                            
                                        ][$tank->fuel_type] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ strtoupper($tank->fuel_type) }}
                                    </span>
                                </td>
                                <td>{{ number_format($tank->capacity) }} L</td>
                                <td>{{ number_format($tank->current_volume, 0) }} L</td>
                                <td>
                                    @php
                                        $percentage = $tank->capacity > 0 ? ($tank->current_volume / $tank->capacity) * 100 : 0;
                                    @endphp
                                    <div class="progress progress-sm">
                                        <div class="progress-bar 
                                            @if($percentage > 80) bg-success
                                            @elseif($percentage > 50) bg-info
                                            @elseif($percentage > 20) bg-warning
                                            @else bg-danger
                                            @endif" 
                                            style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                    <small>{{ number_format($percentage, 1) }}%</small>
                                </td>
                                <td>{{ $tank->description ?? '-' }}</td>
                               
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Total : {{ $tanks->count() }} cuve(s)</strong>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('manager.stocks.dashboard') }}" class="btn btn-default">
                            <i class="fas fa-tachometer-alt mr-1"></i> Tableau de bord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    border-top: 2px solid #FF7F00;
}

.progress {
    height: 20px;
    margin-bottom: 5px;
}

.progress-bar {
    border-radius: 3px;
}
</style>
@endpush
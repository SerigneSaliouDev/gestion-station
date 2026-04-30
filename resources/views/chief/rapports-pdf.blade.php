@extends('layouts.app')

@section('title', 'Génération de Rapports PDF')
@section('page-title', 'Génération de Rapports PDF')
@section('page-icon', 'fa-file-pdf')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('chief.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rapports PDF</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header chief-bg-primary">
                    <h3 class="card-title">
                        <i class="fas fa-file-pdf mr-2"></i> 
                        Sélectionner un Rapport PDF à Générer
                    </h3>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Rapport de Station -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow stats-card">
                                <div class="card-header bg-info text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-gas-pump mr-2"></i> Rapport de Station
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Rapport détaillé d'une station spécifique ou global.</p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i> Statistiques principales</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Ventes par carburant</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Stocks actuels</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <!-- Lien avec nom court -->
                                    <a href="{{ route('pdf.station.report') }}" class="btn btn-info btn-block">
                                        <i class="fas fa-download mr-2"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rapport de Réconciliation -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow stats-card">
                                <div class="card-header bg-warning text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-balance-scale mr-2"></i> Rapport de Réconciliation
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Rapport des mouvements de stock.</p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i> Mouvements détaillés</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Récapitulatif des totaux</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Stocks avant/après</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <!-- Lien avec nom court -->
                                    <a href="{{ route('pdf.reconciliation.report') }}" class="btn btn-warning btn-block">
                                        <i class="fas fa-download mr-2"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rapport d'Inventaire -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow stats-card">
                                <div class="card-header bg-success text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-clipboard-list mr-2"></i> Rapport d'Inventaire
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Rapport des jaugeages et contrôles d'inventaire.</p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i> Jaugeages récents</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Écarts théorique/physique</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Statistiques d'écarts</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <!-- Lien avec nom court -->
                                    <a href="{{ route('pdf.inventory.report') }}" class="btn btn-success btn-block">
                                        <i class="fas fa-download mr-2"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ventes par Pompiste -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow stats-card">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-user-tie mr-2"></i> Ventes par Pompiste
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Analyse des performances des pompistes.</p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i> Classement des pompistes</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Ventes et volumes</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Écart moyen</li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <!-- Lien avec nom court -->
                                    <a href="{{ route('pdf.sales-by-pump.report') }}" class="btn btn-primary btn-block">
                                        <i class="fas fa-download mr-2"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rapport Shift -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow stats-card">
                                <div class="card-header bg-purple text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-file-alt mr-2"></i> Rapport Shift
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Rapport individuel détaillé d'un shift.</p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i> Informations du shift</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Détails des pompes</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Dépenses du shift</li>
                                    </ul>
                                    <div class="mt-3">
                                        <!-- Formulaire avec nom court -->
                                        <form action="{{ route('pdf.shift.report') }}" method="GET" class="form-inline">
                                            <div class="input-group">
                                                <input type="number" name="shiftId" class="form-control" placeholder="ID du Shift" required>
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-purple">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formulaire Avancé -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow stats-card">
                                <div class="card-header bg-secondary text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-cogs mr-2"></i> Options Avancées
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Générez un rapport avec des paramètres spécifiques.</p>
                                    
                                    <form action="{{ route('pdf.generer') }}" method="POST" class="mt-3">
                                        @csrf
                                        <div class="form-group">
                                            <label for="report_type_adv">Type de Rapport</label>
                                            <select class="form-control" id="report_type_adv" name="report_type" required>
                                                <option value="station">Rapport de Station</option>
                                                <option value="reconciliation">Réconciliation</option>
                                                <option value="inventory">Inventaire</option>
                                                <option value="sales-by-pump">Ventes par Pompiste</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="station_id_adv">Station (optionnel)</label>
                                            <select class="form-control" id="station_id_adv" name="station_id">
                                                <option value="">Toutes les stations</option>
                                                @foreach(\App\Models\Station::all() as $station)
                                                    <option value="{{ $station->id }}">{{ $station->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-secondary btn-block">
                                            <i class="fas fa-cog mr-2"></i> Générer avec Options
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <h5><i class="fas fa-info-circle mr-2"></i> Informations</h5>
                        <ul class="mb-0">
                            <li>Les rapports PDF sont générés en temps réel avec les dernières données</li>
                            <li>Les rapports s'ouvrent dans un nouvel onglet pour prévisualisation</li>
                            <li>Pour des périodes personnalisées, utilisez le formulaire "Options Avancées"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    
    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }
    
    .btn-purple:hover {
        background-color: #5936a3;
        border-color: #5936a3;
        color: white;
    }
</style>
@endpush
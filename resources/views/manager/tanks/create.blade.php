{{-- resources/views/manager/tanks/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Créer une cuve')
@section('page-title', 'Création de cuve')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('manager.stocks.dashboard') }}">Tableau de bord</a></li>
<li class="breadcrumb-item"><a href="{{ route('manager.tanks.index') }}">Cuves</a></li>
<li class="breadcrumb-item active">Créer</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #333, #555); color: white;">
                <h3 class="card-title">
                    <i class="fas fa-oil-can mr-2"></i>Créer une nouvelle cuve
                </h3>
                <div class="card-tools">
                    <span class="badge" style="background-color: #FF7F00;">ODYSSEE ENERGIE SA</span>
                </div>
            </div>
            
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Erreurs de validation</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <form method="POST" action="{{ route('manager.tanks.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="number">Numéro de cuve *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-hashtag"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control @error('number') is-invalid @enderror" 
                                           id="number" name="number" 
                                           value="{{ old('number') }}"
                                           required placeholder="Ex: C1, C2, R1">
                                </div>
                                @error('number')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_type">Type de carburant *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-gas-pump"></i>
                                        </span>
                                    </div>
                                    <select class="form-control @error('fuel_type') is-invalid @enderror" 
                                            id="fuel_type" name="fuel_type" required>
                                        <option value="">Sélectionnez...</option>
                                        <option value="super" {{ old('fuel_type') == 'super' ? 'selected' : '' }}>SUPER (Essence)</option>
                                        <option value="gasoil" {{ old('fuel_type') == 'gasoil' ? 'selected' : '' }}>GAZOLE</option>
                                        <option value="essence_pirogue" {{ old('fuel_type') == 'essence_pirogue' ? 'selected' : '' }}>ESSENCE PIROGUE</option>
                                    </select>
                                </div>
                                @error('fuel_type')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="capacity">Capacité (litres) *</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                   id="capacity" name="capacity" 
                                   value="{{ old('capacity') }}"
                                   required min="1000" step="100" 
                                   placeholder="Ex: 10000, 30000">
                            <div class="input-group-append">
                                <span class="input-group-text">Litres</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">Capacité maximale en litres</small>
                        @error('capacity')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" id="description" name="description" 
                                   value="{{ old('description') }}"
                                   placeholder="Ex: Cuve principale, Réservoir de secours">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_safe_level">Niveau minimum de sécurité (cm)</label>
                                <input type="number" class="form-control" id="min_safe_level" name="min_safe_level" 
                                       value="{{ old('min_safe_level', 30) }}"
                                       placeholder="Ex: 30">
                                <small class="form-text text-muted">Hauteur minimale avant alerte</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_safe_level">Niveau maximum de sécurité (cm)</label>
                                <input type="number" class="form-control" id="max_safe_level" name="max_safe_level" 
                                       value="{{ old('max_safe_level', 250) }}"
                                       placeholder="Ex: 250">
                                <small class="form-text text-muted">Hauteur maximale avant alerte</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tolerance_threshold">Seuil de tolérance (%)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="tolerance_threshold" name="tolerance_threshold" 
                                   value="{{ old('tolerance_threshold', 0.5) }}" step="0.1"
                                   placeholder="Ex: 0.5">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">Tolérance pour les écarts de jaugeage</small>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('manager.tanks.index') }}" class="btn btn-default">
                                <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
                            </a>
                            <a href="{{ route('manager.stocks.dashboard') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-home mr-1"></i> Tableau de bord
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="submit" class="btn" style="background-color: #FF7F00; color: white; border-color: #FF7F00;">
                                <i class="fas fa-save mr-1"></i> Enregistrer la cuve
                            </button>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #FF7F00;">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2" style="color: #FF7F00;"></i>Informations
                </h3>
            </div>
            <div class="card-body">
                <h5><i class="fas fa-lightbulb mr-2" style="color: #FF7F00;"></i>Conseils</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success mr-2"></i>Donnez un numéro unique à chaque cuve</li>
                    <li><i class="fas fa-check text-success mr-2"></i>Vérifiez la capacité réelle de la cuve</li>
                    <li><i class="fas fa-check text-success mr-2"></i>Définissez des seuils de sécurité réalistes</li>
                    <li><i class="fas fa-check text-success mr-2"></i>La tolérance par défaut est de 0.5%</li>
                </ul>
                
                <hr>
                
                <h5><i class="fas fa-gas-pump mr-2" style="color: #FF7F00;"></i>Types de carburant</h5>
                <div class="small">
                    <span class="badge badge-danger">SUPER</span> Essence <br>
                    <span class="badge badge-success">GAZOLE</span> Gazole <br>
                    <span class="badge badge-primary">Essence Pirogue</span> Essence Pirogue<br>
                    
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-oil-can fa-3x" style="color: #FF7F00;"></i>
                </div>
                <h5>ODYSSEE ENERGIE SA</h5>
                <p class="text-muted small">Système de gestion des stocks</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-header {
    border-radius: 8px 8px 0 0 !important;
}

.btn-primary {
    background-color: #FF7F00;
    border-color: #FF7F00;
}

.btn-primary:hover {
    background-color: #e67300;
    border-color: #e67300;
}

.badge-danger { background-color: #dc3545; }
.badge-success { background-color: #28a745; }
.badge-primary { background-color: #007bff; }
.badge-warning { background-color: #ffc107; color: #000; }

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}
</style>
@endpush
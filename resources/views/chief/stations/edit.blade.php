@extends('layouts.chief')

@section('title', 'Modifier la Station')
@section('page-icon', 'fa-edit')
@section('page-title', 'Modifier la Station')
@section('page-subtitle', 'Mettre à jour les informations')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('chief.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('chief.stations') }}">Stations</a></li>
    <li class="breadcrumb-item"><a href="{{ route('chief.stations.show', $station->id) }}">{{ $station->nom }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Modifier la station : <strong>{{ $station->nom }}</strong>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('chief.stations.show', $station->id) }}" class="btn btn-tool text-info" title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('chief.stations.update', $station->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <!-- Informations de base -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nom">Nom de la station *</label>
                                    <input type="text" 
                                           class="form-control @error('nom') is-invalid @enderror" 
                                           id="nom" 
                                           name="nom" 
                                           value="{{ old('nom', $station->nom) }}"
                                           required>
                                    @error('nom')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Code de la station *</label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $station->code) }}"
                                           required>
                                    @error('code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="text-muted">Code unique d'identification (ex: A, B, C)</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Localisation -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ville">Ville *</label>
                                    <input type="text" 
                                           class="form-control @error('ville') is-invalid @enderror" 
                                           id="ville" 
                                           name="ville" 
                                           value="{{ old('ville', $station->ville) }}"
                                           required>
                                    @error('ville')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="adresse">Adresse complète *</label>
                                    <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                              id="adresse" 
                                              name="adresse" 
                                              rows="2"
                                              required>{{ old('adresse', $station->adresse) }}</textarea>
                                    @error('adresse')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telephone">Téléphone</label>
                                    <input type="text" 
                                           class="form-control @error('telephone') is-invalid @enderror" 
                                           id="telephone" 
                                           name="telephone" 
                                           value="{{ old('telephone', $station->telephone) }}">
                                    @error('telephone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $station->email) }}">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Capacités -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="capacite_super">Capacité Super (litres) *</label>
                                    <input type="number" 
                                           class="form-control @error('capacite_super') is-invalid @enderror" 
                                           id="capacite_super" 
                                           name="capacite_super" 
                                           value="{{ old('capacite_super', $station->capacite_super) }}"
                                           min="0"
                                           step="100"
                                           required>
                                    @error('capacite_super')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="capacite_gazole">Capacité Gazole (litres) *</label>
                                    <input type="number" 
                                           class="form-control @error('capacite_gazole') is-invalid @enderror" 
                                           id="capacite_gazole" 
                                           name="capacite_gazole" 
                                           value="{{ old('capacite_gazole', $station->capacite_gazole) }}"
                                           min="0"
                                           step="100"
                                           required>
                                    @error('capacite_gazole')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Manager assigné -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="manager_id">Manager assigné</label>
                                    <select class="form-control @error('manager_id') is-invalid @enderror" 
                                            id="manager_id" 
                                            name="manager_id">
                                        <option value="">Aucun manager</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" 
                                                {{ old('manager_id', $station->manager_id) == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }} ({{ $manager->email }})
                                                @if($manager->station_id == $station->id)
                                                    - Actuellement assigné
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('manager_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="statut">Statut de la station *</label>
                                    <select class="form-control @error('statut') is-invalid @enderror" 
                                            id="statut" 
                                            name="statut"
                                            required>
                                        <option value="actif" {{ old('statut', $station->statut) == 'actif' ? 'selected' : '' }}>Active</option>
                                        <option value="inactif" {{ old('statut', $station->statut) == 'inactif' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('statut')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations sur le manager actuel -->
                        @if($station->manager)
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            Manager actuellement assigné : 
                            <strong>{{ $station->manager->name }}</strong> ({{ $station->manager->email }})
                            <br>
                            <small>Changer de manager réassignera automatiquement le nouveau manager et libérera l'ancien.</small>
                        </div>
                        @endif
                    </div>
                    
                    <div class="card-footer">
                        <div class="float-right">
                            <a href="{{ route('chief.stations.show', $station->id) }}" class="btn btn-default mr-2">
                                <i class="fas fa-times mr-1"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Colonne latérale -->
        <div class="col-md-4">
            <!-- Aperçu des modifications -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i> Aperçu
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-light">
                        <span class="info-box-icon">
                            <i class="fas fa-gas-pump text-info"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Capacité Totale</span>
                            <span class="info-box-number">
                                <span id="previewTotalCapacity">
                                    {{ number_format($station->capacite_super + $station->capacite_gazole, 0, ',', ' ') }}
                                </span> L
                            </span>
                        </div>
                    </div>
                    
                    <table class="table table-sm">
                        <tr>
                            <td>Capacité Super:</td>
                            <td class="text-right">
                                <span id="previewSuper">{{ number_format($station->capacite_super, 0, ',', ' ') }}</span> L
                            </td>
                        </tr>
                        <tr>
                            <td>Capacité Gazole:</td>
                            <td class="text-right">
                                <span id="previewGazole">{{ number_format($station->capacite_gazole, 0, ',', ' ') }}</span> L
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Actions de gestion -->
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Zone de danger
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            Les actions ci-dessous sont irréversibles. Utilisez-les avec prudence.
                        </small>
                    </p>
                    
                    <!-- Supprimer la station -->
                    <form method="POST" action="{{ route('chief.stations.destroy', $station->id) }}" 
                          id="deleteForm" 
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette station? Cette action est irréversible!');">
                        @csrf
                        @method('DELETE')
                        
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash mr-1"></i> Supprimer cette station
                        </button>
                    </form>
                    
                    <!-- Désactiver la station -->
                    @if($station->statut == 'actif')
                    <form method="POST" action="{{ route('chief.stations.update', $station->id) }}" 
                          class="mt-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="statut" value="inactif">
                        <input type="hidden" name="nom" value="{{ $station->nom }}">
                        <input type="hidden" name="code" value="{{ $station->code }}">
                        <input type="hidden" name="ville" value="{{ $station->ville }}">
                        <input type="hidden" name="adresse" value="{{ $station->adresse }}">
                        <input type="hidden" name="capacite_super" value="{{ $station->capacite_super }}">
                        <input type="hidden" name="capacite_gazole" value="{{ $station->capacite_gazole }}">
                        
                        <button type="submit" 
                                class="btn btn-warning btn-block"
                                onclick="return confirm('Désactiver cette station? Les managers ne pourront plus y accéder.');">
                            <i class="fas fa-power-off mr-1"></i> Désactiver la station
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('chief.stations.update', $station->id) }}" 
                          class="mt-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="statut" value="actif">
                        <input type="hidden" name="nom" value="{{ $station->nom }}">
                        <input type="hidden" name="code" value="{{ $station->code }}">
                        <input type="hidden" name="ville" value="{{ $station->ville }}">
                        <input type="hidden" name="adresse" value="{{ $station->adresse }}">
                        <input type="hidden" name="capacite_super" value="{{ $station->capacite_super }}">
                        <input type="hidden" name="capacite_gazole" value="{{ $station->capacite_gazole }}">
                        
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check mr-1"></i> Activer la station
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculer et mettre à jour la capacité totale
    function updateTotalCapacity() {
        var superCap = parseInt($('#capacite_super').val()) || 0;
        var gazoleCap = parseInt($('#capacite_gazole').val()) || 0;
        var total = superCap + gazoleCap;
        
        $('#previewSuper').text(superCap.toLocaleString('fr-FR'));
        $('#previewGazole').text(gazoleCap.toLocaleString('fr-FR'));
        $('#previewTotalCapacity').text(total.toLocaleString('fr-FR'));
    }
    
    // Écouter les changements sur les champs de capacité
    $('#capacite_super, #capacite_gazole').on('input', updateTotalCapacity);
    
    // Initialiser le calcul
    updateTotalCapacity();
    
    // Confirmation avant suppression
    $('#deleteForm').on('submit', function(e) {
        var shiftsCount = {{ $station->shifts()->count() ?? 0 }};
        
        if (shiftsCount > 0) {
            e.preventDefault();
            alert('Impossible de supprimer cette station car elle a ' + shiftsCount + ' shift(s) associé(s).');
            return false;
        }
        
        return confirm('Êtes-vous ABSOLUMENT sûr de vouloir supprimer cette station? Cette action est IRREVERSIBLE!');
    });
});
</script>
@endpush
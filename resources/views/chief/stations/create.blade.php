@extends('layouts.chief')

@section('title', 'Ajouter une Station')
@section('page-icon', 'fa-gas-pump')
@section('page-title', 'Ajouter une Station')
@section('page-subtitle', 'Créer une nouvelle station de carburant')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('chief.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('chief.stations.index') }}">Stations</a></li>
    <li class="breadcrumb-item active">Ajouter</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Nouvelle Station</h3>
            </div>
            <form action="{{ route('chief.stations.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nom">Nom de la station *</label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" name="nom" value="{{ old('nom') }}" required>
                                @error('nom')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Code unique *</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}" required>
                                <small class="text-muted">Ex: ST001, ST002</small>
                                @error('code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ville">Ville *</label>
                                <input type="text" class="form-control @error('ville') is-invalid @enderror" 
                                       id="ville" name="ville" value="{{ old('ville') }}" required>
                                @error('ville')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="adresse">Adresse</label>
                                <input type="text" class="form-control @error('adresse') is-invalid @enderror" 
                                       id="adresse" name="adresse" value="{{ old('adresse') }}">
                                @error('adresse')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telephone">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror" 
                                       id="telephone" name="telephone" value="{{ old('telephone') }}">
                                @error('telephone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- CAPACITÉS DES CUVES -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Capacités des cuves (en litres)</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="capacite_super">Super (SP95/SP98) *</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" 
                                                   class="form-control @error('capacite_super') is-invalid @enderror" 
                                                   id="capacite_super" name="capacite_super" 
                                                   value="{{ old('capacite_super', 0) }}" required min="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Litres</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Capacité totale des cuves de Super</small>
                                        @error('capacite_super')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="capacite_gazole">Gazole *</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" 
                                                   class="form-control @error('capacite_gazole') is-invalid @enderror" 
                                                   id="capacite_gazole" name="capacite_gazole" 
                                                   value="{{ old('capacite_gazole', 0) }}" required min="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Litres</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Capacité totale des cuves de Gazole</small>
                                        @error('capacite_gazole')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Capacité supplémentaire optionnelle -->
                           
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="capacite_petrole">Pétrole</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" 
                                                   class="form-control @error('capacite_petrole') is-invalid @enderror" 
                                                   id="capacite_petrole" name="capacite_petrole" 
                                                   value="{{ old('capacite_petrole', 0) }}" min="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Litres</span>
                                            </div>
                                        </div>
                                        @error('capacite_petrole')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_id">Manager assigné</label>
                                <select class="form-control @error('manager_id') is-invalid @enderror" 
                                        id="manager_id" name="manager_id">
                                    <option value="">-- Aucun manager --</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}" 
                                                {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }} ({{ $manager->email }})
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
                                <label for="statut">Statut</label>
                                <select class="form-control @error('statut') is-invalid @enderror" 
                                        id="statut" name="statut">
                                    <option value="actif" {{ old('statut', 'actif') == 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                                    <option value="maintenance" {{ old('statut') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('statut')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes supplémentaires</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer la station
                    </button>
                    <a href="{{ route('chief.stations.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calcul automatique de la capacité totale
    function calculateTotalCapacity() {
        var superCap = parseFloat($('#capacite_super').val()) || 0;
        var gazoleCap = parseFloat($('#capacite_gazole').val()) || 0;
        var keroseneCap = parseFloat($('#capacite_kerosene').val()) || 0;
        var petroleCap = parseFloat($('#capacite_petrole').val()) || 0;
        
        var total = superCap + gazoleCap + keroseneCap + petroleCap;
        return total;
    }
    
    // Afficher la capacité totale en temps réel
    function updateTotalCapacity() {
        var total = calculateTotalCapacity();
        $('#total-capacity-display').text(total.toLocaleString('fr-FR') + ' litres');
    }
    
    // Écouter les changements sur les champs de capacité
    $('[id^="capacite_"]').on('input', updateTotalCapacity);
    
    // Initialiser l'affichage
    updateTotalCapacity();
});
</script>
@endpush
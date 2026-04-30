@extends('layouts.admin')

@section('title', 'Créer une Station')
@section('page-title', 'Nouvelle Station')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.stations.index') }}">Stations</a></li>
<li class="breadcrumb-item active">Créer</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Créer une nouvelle station</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.stations.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nom">Nom de la station *</label>
                                <input type="text" 
                                       class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" 
                                       name="nom" 
                                       value="{{ old('nom') }}"
                                       required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Code station *</label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code') }}"
                                       required>
                                <small class="form-text text-muted">Code unique pour identifier la station (ex: ST001)</small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ville">Ville *</label>
                                <input type="text" 
                                       class="form-control @error('ville') is-invalid @enderror" 
                                       id="ville" 
                                       name="ville" 
                                       value="{{ old('ville') }}"
                                       required
                                       list="ville-suggestions">
                                <datalist id="ville-suggestions">
                                    @foreach($villes as $ville)
                                        <option value="{{ $ville }}">
                                    @endforeach
                                </datalist>
                                @error('ville')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="adresse">Adresse *</label>
                                <input type="text" 
                                       class="form-control @error('adresse') is-invalid @enderror" 
                                       id="adresse" 
                                       name="adresse" 
                                       value="{{ old('adresse') }}"
                                       required>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_id">Manager *</label>
                                <select class="form-control @error('manager_id') is-invalid @enderror" 
                                        id="manager_id" 
                                        name="manager_id">
                                    <option value="">Sélectionner un manager</option>
                                    @if($managers->count() > 0)
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }} ({{ $manager->email }})
                                                @if($manager->station_id) 
                                                    - Déjà manager de {{ $manager->station->nom ?? 'une station' }}
                                                @else
                                                    - Disponible
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucun manager disponible</option>
                                    @endif
                                </select>
                                @if($managers->count() == 0)
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Aucun manager trouvé. 
                                        <a href="{{ route('admin.users.create') }}" class="text-primary">
                                            Créez-en un d'abord
                                        </a>
                                    </small>
                                @else
                                    <small class="form-text text-muted">
                                        Sélectionnez un manager pour cette station.
                                    </small>
                                @endif
                                @error('manager_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="statut">Statut *</label>
                                <select class="form-control @error('statut') is-invalid @enderror" 
                                        id="statut" 
                                        name="statut"
                                        required>
                                    <option value="">Sélectionner un statut</option>
                                    <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                                    <option value="maintenance" {{ old('statut') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telephone">Téléphone</label>
                                <input type="text" 
                                       class="form-control @error('telephone') is-invalid @enderror" 
                                       id="telephone" 
                                       name="telephone" 
                                       value="{{ old('telephone') }}">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacite_super">Capacité Super (L)</label>
                                <input type="number" 
                                       class="form-control @error('capacite_super') is-invalid @enderror" 
                                       id="capacite_super" 
                                       name="capacite_super" 
                                       value="{{ old('capacite_super', 0) }}"
                                       min="0" 
                                       step="100">
                                @error('capacite_super')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacite_gazole">Capacité Gazole (L)</label>
                                <input type="number" 
                                       class="form-control @error('capacite_gazole') is-invalid @enderror" 
                                       id="capacite_gazole" 
                                       name="capacite_gazole" 
                                       value="{{ old('capacite_gazole', 0) }}"
                                       min="0" 
                                       step="100">
                                @error('capacite_gazole')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacite_essence_pirogue">Capacité Essence Pirogue (L)</label>
                                <input type="number" 
                                       class="form-control @error('capacite_essence_pirogue') is-invalid @enderror" 
                                       id="capacite_essence_pirogue" 
                                       name="capacite_essence_pirogue" 
                                       value="{{ old('capacite_essence_pirogue', 0) }}"
                                       min="0" 
                                       step="100">
                                @error('capacite_essence_pirogue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group text-right mt-4">
                        <a href="{{ route('admin.stations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Créer la station
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <div class="alert alert-info mb-0">
                    <h6><i class="fas fa-info-circle"></i> Informations importantes</h6>
                    <ul class="mb-0">
                        <li>Les champs marqués d'un * sont obligatoires</li>
                        <li>Le code station doit être unique</li>
                        <li>Un manager peut être assigné ultérieurement si non disponible maintenant</li>
                        <li>Après la création, vous pourrez configurer les pompes et les prix</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-format du code station en majuscules
        $('#code').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });
        
        // Suggestion de ville
        $('#ville').on('input', function() {
            var input = $(this).val();
            if (input.length > 2) {
                // Vous pourriez ajouter une requête AJAX ici pour des suggestions plus dynamiques
            }
        });
        
        // Message d'aide pour les managers
        $('#manager_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            if (selectedOption.text().includes('Déjà manager')) {
                if (!confirm('Ce manager est déjà assigné à une station. Voulez-vous le réassigner à cette nouvelle station ?')) {
                    $(this).val('');
                }
            }
        });
    });
</script>
@endpush
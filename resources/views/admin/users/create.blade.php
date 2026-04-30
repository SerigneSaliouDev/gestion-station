@extends('layouts.admin')

@section('title', 'Créer un nouvel utilisateur')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus text-primary"></i> Créer un nouvel utilisateur
        </h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-circle"></i> Informations de l'utilisateur
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nom complet *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required 
                                           placeholder="Prénom et Nom">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required
                                           placeholder="exemple@odysse-energie.com">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="statut">Statut *</label>
                                    <select name="statut" id="statut" class="form-control @error('statut') is-invalid @enderror" required>
                                        <option value="">Sélectionnez un statut</option>
                                        <option value="active" {{ old('statut') == 'active' ? 'selected' : '' }}>
                                            Actif
                                        </option>
                                        <option value="inactive" {{ old('statut') == 'inactive' ? 'selected' : '' }}>
                                            Inactif
                                        </option>
                                        <option value="pending" {{ old('statut') == 'pending' ? 'selected' : '' }}>
                                            En attente
                                        </option>
                                    </select>
                                    @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Rôle *</label>
                                    <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                                        <option value="">Sélectionnez un rôle</option>
                                        <option value="administrateur" {{ old('role') == 'administrateur' ? 'selected' : '' }}>
                                            Administrateur
                                        </option>
                                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>
                                            Manager
                                        </option>
                                        <option value="chief" {{ old('role') == 'chief' ? 'selected' : '' }}>
                                            Chef des opérations
                                        </option>
                                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>
                                            Utilisateur
                                        </option>
                                    </select>
                                    @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Définit les permissions de l'utilisateur
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SECTION STATION - VISIBLE SEULEMENT POUR CERTAINS RÔLES -->
                        <div class="row" id="station-section" style="display: none;">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="station_id">Station assignée</label>
                                    <select name="station_id" id="station_id" class="form-control @error('station_id') is-invalid @enderror">
                                        <option value="">Sélectionnez une station</option>
                                        @foreach($stations as $station)
                                        <option value="{{ $station->id }}" {{ old('station_id') == $station->id ? 'selected' : '' }}>
                                            {{ $station->nom }} ({{ $station->code }}) - {{ $station->ville }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Optionnel pour certains rôles. Les managers seront assignés via la création/modification de station.
                                    </small>
                                    @error('station_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="send_welcome_email" name="send_welcome_email" value="1" checked>
                                <label class="custom-control-label" for="send_welcome_email">
                                    Envoyer un email de bienvenue avec le mot de passe temporaire
                                </label>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Information :</strong> Un mot de passe temporaire sera généré automatiquement et pourra être modifié par l'utilisateur lors de sa première connexion.
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer l'utilisateur
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Réinitialiser
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb"></i> Guide des rôles
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="font-weight-bold text-primary">
                            <i class="fas fa-user-shield"></i> Administrateur
                        </h6>
                        <p class="small text-muted">
                            Accès complet au système. Gère tous les utilisateurs, stations et configurations.
                            <strong>Pas de station assignée.</strong>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold text-success">
                            <i class="fas fa-user-tie"></i> Manager
                        </h6>
                        <p class="small text-muted">
                            Gère <strong>une seule station</strong>. Valide les shifts et consulte les rapports de sa station.
                            <strong>Doit être assigné à une station spécifique.</strong>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold text-warning">
                            <i class="fas fa-user-cog"></i> Chef des opérations
                        </h6>
                        <p class="small text-muted">
                            Vue globale sur <strong>toutes les stations</strong>. Supervise les opérations quotidiennes.
                            <strong>Pas de station assignée spécifique.</strong>
                        </p>
                    </div>
                    
                   
                    
                    <hr>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important :</strong>
                        <ul class="mb-0 mt-2">
                            <li>Tous les champs marqués d'un * sont obligatoires</li>
                            <li>L'email doit être unique dans le système</li>
                            <li>Seuls les managers doivent être assignés à une station</li>
                            <li>Une station ne peut avoir qu'un seul manager à la fois</li>
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
    .card {
        border-radius: 10px;
    }
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const stationSection = document.getElementById('station-section');
        const stationSelect = document.getElementById('station_id');
        
        // Gérer l'affichage de la section station
        function toggleStationSection() {
            const selectedRole = roleSelect.value;
            
            // Rôles qui peuvent avoir une station optionnelle
            const rolesAvecStationOptionnelle = ['user']; // Seuls les 'user' peuvent avoir une station optionnelle
            
            if (rolesAvecStationOptionnelle.includes(selectedRole)) {
                stationSection.style.display = 'block';
                stationSelect.required = false; // Optionnel
            } else {
                // Managers, admins, chiefs n'ont pas de station à la création
                stationSection.style.display = 'none';
                stationSelect.required = false;
                stationSelect.value = '';
            }
        }
        
        // Événements
        roleSelect.addEventListener('change', function() {
            toggleStationSection();
        });
        
        // Initialiser au chargement
        toggleStationSection();
        
        // Validation en temps réel de l'email
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('blur', function() {
            const email = this.value;
            if (email && !isValidEmail(email)) {
                this.classList.add('is-invalid');
                let feedback = this.nextElementSibling;
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    this.parentNode.appendChild(feedback);
                }
                feedback.textContent = 'Format d\'email invalide';
            }
        });
        
        emailInput.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
            }
        });
        
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    });
</script>
@endpush
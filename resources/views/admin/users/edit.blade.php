@extends('layouts.admin')

@section('title', 'Éditer Utilisateur: ' . $user->name)
@section('page-title', 'Éditer Utilisateur')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></li>
<li class="breadcrumb-item active">Éditer</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Éditer l'utilisateur: {{ $user->name }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Voir
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nom complet *</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role">Rôle *</label>
                                <select class="form-control @error('role') is-invalid @enderror" 
                                        id="role" 
                                        name="role"
                                        required>
                                    <option value="">Sélectionner un rôle</option>
                                    @foreach($roles as $value => $label)
                                        <option value="{{ $value }}" 
                                            {{ old('role', $user->role) == $value ? 'selected' : '' }}>
                                            {{ ucfirst($label) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
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
                                    <option value="active" {{ old('statut', $user->statut) == 'active' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactive" {{ old('statut', $user->statut) == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                    <option value="pending" {{ old('statut', $user->statut) == 'pending' ? 'selected' : '' }}>En attente</option>
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
                                <label for="station_id">Station assignée</label>
                                <select class="form-control @error('station_id') is-invalid @enderror" 
                                        id="station_id" 
                                        name="station_id">
                                    <option value="">Non assigné</option>
                                    @foreach($stations as $station)
                                        <option value="{{ $station->id }}" 
                                            {{ old('station_id', $user->station_id) == $station->id ? 'selected' : '' }}>
                                            {{ $station->nom }} ({{ $station->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('station_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mt-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="reset_password" name="reset_password" value="1">
                            <label class="custom-control-label" for="reset_password">
                                Réinitialiser le mot de passe
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Si coché, un nouveau mot de passe sera généré (mot de passe par défaut: Saliou2003)
                        </small>
                    </div>
                    
                    <div class="form-group text-right mt-4">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Gestion dynamique des champs selon le rôle
        $('#role').on('change', function() {
            var role = $(this).val();
            var stationField = $('#station_id');
            
            if (role === 'manager') {
                // Pour les managers, la station est importante
                stationField.closest('.form-group').find('label').addClass('text-primary font-weight-bold');
                stationField.attr('data-required', 'true');
            } else {
                stationField.closest('.form-group').find('label').removeClass('text-primary font-weight-bold');
                stationField.removeAttr('data-required');
            }
        });
        
        // Initialiser
        $('#role').trigger('change');
    });
</script>
@endpush
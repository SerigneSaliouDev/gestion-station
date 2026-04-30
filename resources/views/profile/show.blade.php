@php
    $layout = 'app-layout'; 
    if (Auth::user()->hasRole('administrateur')) {
        $layout = 'admin-layout';
    } elseif (Auth::user()->hasRole('manager')) {
        $layout = 'app-layout';
    } elseif (Auth::user()->hasRole('charge-operations')) {
        $layout = 'operations-layout';
    }
@endphp

<x-dynamic-component :component="$layout">
    @section('page-title', 'Mon Espace Personnel')
    
    @section('breadcrumb')
        <li class="breadcrumb-item active">
            <i class="fas fa-user-circle mr-1"></i> {{ __('Profil') }}
        </li>
    @endsection

    <div class="container-fluid">
        <!-- Header Card with User Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    @if(Auth::user()->profile_photo_url)
                                        <img src="{{ Auth::user()->profile_photo_url }}" alt="Avatar" class="img-circle elevation-2" style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-orange rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-user fa-2x text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="mb-0 text-white">{{ Auth::user()->name }}</h3>
                                    <p class="mb-0 text-white-50">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                            <div>
                                <span class="badge badge-warning badge-lg px-3 py-2">
                                    <i class="fas fa-tag mr-1"></i> 
                                    {{ Auth::user()->roles->first()->name ?? 'Utilisateur' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Cards -->
        <div class="row">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                <div class="col-md-6 mb-4">
                    <div class="card card-outline card-orange h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-id-card mr-2 text-orange"></i>
                                {{ __('Informations du Profil') }}
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                <i class="fas fa-info-circle mr-1"></i> 
                                {{ __('Mettez à jour vos coordonnées et votre identité.') }}
                            </p>
                            <div class="profile-update-form-wrapper">
                                @livewire('profile.update-profile-information-form')
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="col-md-6 mb-4">
                    <div class="card card-outline card-success h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lock mr-2 text-success"></i>
                                {{ __('Sécurité du Compte') }}
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                <i class="fas fa-shield-alt mr-1"></i> 
                                {{ __('Assurez-vous que votre compte utilise un mot de passe robuste.') }}
                            </p>
                            <div class="password-update-form-wrapper">
                                @livewire('profile.update-password-form')
                            </div>
                        </div>
                    </div>
                </div>
            @endif

                
            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <div class="col-md-6 mb-4">
                    <div class="card card-outline card-danger h-100">
                        <div class="card-header bg-danger text-white">
                            <h3 class="card-title text-white">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                {{ __('Zone de Danger') }}
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                <i class="fas fa-exclamation-circle text-danger mr-1"></i> 
                                <strong class="text-danger">{{ __('Action irréversible') }}</strong> : 
                                {{ __('suppression définitive de toutes vos données.') }}
                            </p>
                            <div class="delete-form-wrapper">
                                @livewire('profile.delete-user-form')
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-dynamic-component>

@push('styles')
<style>
    /* Styles pour garantir que les boutons sont visibles */
    .card-body button[type="submit"],
    .profile-update-form-wrapper button,
    .password-update-form-wrapper button,
    .sessions-form-wrapper button,
    .delete-form-wrapper button {
        background-color: #FF7F00 !important;
        border-color: #FF7F00 !important;
        color: white !important;
        padding: 8px 20px !important;
        border-radius: 4px !important;
        font-weight: 600 !important;
        margin-top: 15px !important;
        display: inline-block !important;
        cursor: pointer !important;
    }

    .card-body button[type="submit"]:hover,
    .profile-update-form-wrapper button:hover,
    .password-update-form-wrapper button:hover,
    .sessions-form-wrapper button:hover,
    .delete-form-wrapper button:hover {
        background-color: #e67300 !important;
        border-color: #e67300 !important;
        transform: translateY(-1px);
        transition: all 0.3s ease;
    }

    /* Style spécifique pour le bouton de suppression */
    .delete-form-wrapper button {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
    }

    .delete-form-wrapper button:hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
    }

    /* Style pour les boutons secondaires */
    .card-body button[type="button"] {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: white !important;
    }

    /* Amélioration des champs de formulaire */
    .card-body input,
    .card-body textarea,
    .card-body select {
        border-radius: 4px !important;
        border: 1px solid #ddd !important;
        padding: 8px 12px !important;
        width: 100% !important;
        margin-bottom: 10px !important;
    }

    .card-body input:focus,
    .card-body textarea:focus,
    .card-body select:focus {
        border-color: #FF7F00 !important;
        outline: none !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 127, 0, 0.25) !important;
    }

    /* Style pour les labels */
    .card-body label {
        font-weight: 600 !important;
        margin-bottom: 5px !important;
        display: block !important;
        color: #333 !important;
    }

    /* Espacement entre les champs */
    .card-body .form-group,
    .card-body .space-y-6 > div {
        margin-bottom: 20px !important;
    }

    /* Style pour les messages d'erreur */
    .card-body .text-danger,
    .card-body .error {
        color: #dc3545 !important;
        font-size: 12px !important;
        margin-top: 5px !important;
    }

    /* Style pour les messages de succès */
    .card-body .text-success,
    .card-body .success {
        color: #28a745 !important;
        font-size: 14px !important;
        margin-top: 10px !important;
        padding: 10px !important;
        background-color: #d4edda !important;
        border-radius: 4px !important;
    }

    /* Styles existants */
    .card-orange:not(.card-outline) > .card-header {
        background-color: #FF7F00;
        color: white;
    }
    
    .card-orange.card-outline {
        border-top: 3px solid #FF7F00;
    }
    
    .card-orange .card-header {
        background-color: rgba(255, 127, 0, 0.1);
        border-bottom: 1px solid rgba(255, 127, 0, 0.2);
    }
    
    .text-orange {
        color: #FF7F00 !important;
    }
    
    .bg-orange {
        background-color: #FF7F00 !important;
    }
    
    .badge-warning {
        background-color: #FF7F00;
        color: white;
    }
    
    .card-outline.card-purple {
        border-top: 3px solid #6f42c1;
    }
    
    .card-outline.card-success {
        border-top: 3px solid #28a745;
    }
    
    .card-outline.card-danger {
        border-top: 3px solid #dc3545;
    }
    
    .bg-gradient-dark {
        background: linear-gradient(135deg, #333333 0%, #1a1a1a 100%);
    }
    
    .img-circle {
        border-radius: 50%;
        border: 3px solid #FF7F00;
    }
    
    .card-header .card-title {
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }

    /* Assurer que les wrappers Livewire prennent tout l'espace */
    .profile-update-form-wrapper,
    .password-update-form-wrapper,
    .sessions-form-wrapper,
    .delete-form-wrapper {
        width: 100%;
    }

    /* Style pour les boutons dans les composants Livewire natifs */
    [wire\:submit] button,
    button[wire\:click] {
        background-color: #FF7F00 !important;
        border-color: #FF7F00 !important;
        color: white !important;
        padding: 8px 20px !important;
        border-radius: 4px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
    }

    /* Style spécifique pour les boutons "Save" et "Update" */
    button:contains("Save"),
    button:contains("Update"),
    button:contains("Enregistrer"),
    button:contains("Mettre à jour") {
        background-color: #FF7F00 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Script pour s'assurer que tous les boutons sont visibles après le chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier et styliser tous les boutons dans les formulaires
        const allButtons = document.querySelectorAll('.card-body button, [wire\\:submit] button, button[wire\\:click]');
        
        allButtons.forEach(button => {
            // S'assurer que le bouton est visible
            button.style.display = 'inline-block';
            button.style.visibility = 'visible';
            button.style.opacity = '1';
            
            // Ajouter une classe si nécessaire
            if (!button.classList.contains('btn') && !button.classList.contains('btn-primary')) {
                button.classList.add('btn', 'btn-primary');
            }
        });
        
        // Observer les changements dans le DOM (pour les composants Livewire)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    const newButtons = document.querySelectorAll('.card-body button:not([style*="display"])');
                    newButtons.forEach(button => {
                        button.style.display = 'inline-block';
                        button.style.visibility = 'visible';
                        button.style.opacity = '1';
                        if (!button.classList.contains('btn')) {
                            button.classList.add('btn', 'btn-primary');
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
</script>
@endpush
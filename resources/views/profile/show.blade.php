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
                        <div class="card-header">
                            <h3 class="card-title text-danger">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                {{ __('Zone de Danger') }}
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
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
    /* ============================================ */
    /* STYLES CORRIGÉS POUR BOUTONS CLAIRS ET VISIBLES */
    /* ============================================ */
    
    /* Styles de base pour TOUS les boutons */
    .card-body button,
    .card-body input[type="submit"],
    .card-body input[type="button"],
    .profile-update-form-wrapper button,
    .password-update-form-wrapper button,
    .delete-form-wrapper button,
    [wire\:submit] button,
    button[wire\:click] {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        padding: 10px 24px !important;
        border-radius: 6px !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        border: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    /* BOUTON PRINCIPAL - ORANGE (Save, Update, Enregistrer) */
    .card-body button[type="submit"],
    .profile-update-form-wrapper button[type="submit"],
    .password-update-form-wrapper button[type="submit"],
    button:contains("Save"),
    button:contains("Update"),
    button:contains("Enregistrer"),
    button:contains("Mettre à jour"),
    button:contains("Modifier"),
    [wire\:submit] button {
        background-color: #FF7F00 !important;
        color: white !important;
        border: 2px solid #FF7F00 !important;
    }

    .card-body button[type="submit"]:hover,
    .profile-update-form-wrapper button[type="submit"]:hover,
    .password-update-form-wrapper button[type="submit"]:hover {
        background-color: #e67300 !important;
        border-color: #e67300 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(255, 127, 0, 0.3) !important;
    }

    /* BOUTON SUPPRESSION - ROUGE */
    .delete-form-wrapper button,
    button:contains("Delete"),
    button:contains("Supprimer"),
    button:contains("Confirmer la suppression") {
        background-color: #dc3545 !important;
        color: white !important;
        border: 2px solid #dc3545 !important;
    }

    .delete-form-wrapper button:hover,
    button:contains("Delete"):hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3) !important;
    }

    /* BOUTON SECONDAIRE - GRIS (Annuler, Cancel) */
    .card-body button[type="button"],
    button:contains("Cancel"),
    button:contains("Annuler"),
    button:contains("Fermer") {
        background-color: #6c757d !important;
        color: white !important;
        border: 2px solid #6c757d !important;
    }

    .card-body button[type="button"]:hover {
        background-color: #5a6268 !important;
        transform: translateY(-1px) !important;
    }

    /* CHAMPS DE FORMULAIRES - BIEN VISIBLES */
    .card-body input:not([type="submit"]):not([type="button"]),
    .card-body textarea,
    .card-body select {
        width: 100% !important;
        padding: 10px 12px !important;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        background-color: #fff !important;
        margin-bottom: 15px !important;
        transition: all 0.3s ease !important;
    }

    .card-body input:focus,
    .card-body textarea:focus,
    .card-body select:focus {
        border-color: #FF7F00 !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(255, 127, 0, 0.25) !important;
    }

    /* LABELS - CLAIRS ET VISIBLES */
    .card-body label {
        display: block !important;
        font-weight: 600 !important;
        margin-bottom: 8px !important;
        color: #333 !important;
        font-size: 14px !important;
    }

    /* MESSAGES D'ERREUR */
    .card-body .text-danger,
    .card-body .error {
        color: #dc3545 !important;
        font-size: 12px !important;
        margin-top: 5px !important;
        display: block !important;
    }

    /* MESSAGES DE SUCCÈS */
    .card-body .text-success {
        background-color: #d4edda !important;
        color: #155724 !important;
        padding: 12px !important;
        border-radius: 6px !important;
        margin-top: 15px !important;
        border-left: 4px solid #28a745 !important;
    }

    /* ESPACEMENT DES GROUPES DE FORMULAIRES */
    .card-body .form-group,
    .card-body .space-y-6 > div {
        margin-bottom: 20px !important;
    }

    /* STYLES EXISTANTS CONSERVÉS */
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

    /* ESPACE ENTRE LES BOUTONS DANS UN GROUPE */
    .card-body .flex,
    .card-body .flex-row {
        gap: 12px !important;
    }

    /* BOUTON DÉSACTIVÉ */
    .card-body button:disabled,
    .card-body input:disabled {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Script pour s'assurer que tous les boutons sont correctement stylisés
    document.addEventListener('DOMContentLoaded', function() {
        styliserTousLesBoutons();
        
        // Observer les changements Livewire
        const observer = new MutationObserver(function(mutations) {
            let besoinStyle = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    besoinStyle = true;
                }
            });
            if (besoinStyle) {
                setTimeout(styliserTousLesBoutons, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        function styliserTousLesBoutons() {
            // Récupérer TOUS les boutons dans les cartes
            const cards = document.querySelectorAll('.card-body');
            
            cards.forEach(card => {
                // Boutons normaux
                const buttons = card.querySelectorAll('button, input[type="submit"], input[type="button"]');
                
                buttons.forEach(button => {
                    // S'assurer que le bouton est visible
                    button.style.display = 'inline-block';
                    button.style.visibility = 'visible';
                    button.style.opacity = '1';
                    
                    const buttonText = button.textContent || button.value || '';
                    
                    // Appliquer la classe correcte selon le texte
                    if (buttonText.includes('Supprimer') || buttonText.includes('Delete') || buttonText.includes('Confirmer')) {
                        button.style.backgroundColor = '#dc3545';
                        button.style.borderColor = '#dc3545';
                        button.style.color = 'white';
                    } else if (buttonText.includes('Annuler') || buttonText.includes('Cancel') || buttonText.includes('Fermer')) {
                        button.style.backgroundColor = '#6c757d';
                        button.style.borderColor = '#6c757d';
                        button.style.color = 'white';
                    } else if (button.type === 'submit' || buttonText.includes('Save') || buttonText.includes('Update') || buttonText.includes('Enregistrer')) {
                        button.style.backgroundColor = '#FF7F00';
                        button.style.borderColor = '#FF7F00';
                        button.style.color = 'white';
                    }
                    
                    // Ajouter padding si manquant
                    if (!button.style.padding || button.style.padding === '') {
                        button.style.padding = '10px 24px';
                    }
                    
                    // Ajouter border radius
                    if (!button.style.borderRadius || button.style.borderRadius === '') {
                        button.style.borderRadius = '6px';
                    }
                    
                    // Ajouter font weight
                    if (!button.style.fontWeight || button.style.fontWeight === '') {
                        button.style.fontWeight = '700';
                    }
                    
                    // S'assurer que le bouton n'est pas caché
                    button.classList.add('btn-visible');
                });
            });
            
            // Cibler spécifiquement les boutons Livewire
            const livewireButtons = document.querySelectorAll('[wire\\:submit] button, button[wire\\:click]');
            livewireButtons.forEach(button => {
                button.style.backgroundColor = '#FF7F00';
                button.style.color = 'white';
                button.style.padding = '10px 24px';
                button.style.borderRadius = '6px';
                button.style.fontWeight = '700';
                button.style.border = 'none';
                button.style.cursor = 'pointer';
            });
        }
    });
    
    // Style supplémentaire injecté dynamiquement
    const style = document.createElement('style');
    style.textContent = `
        .btn-visible {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        button, input[type="submit"], input[type="button"] {
            display: inline-block !important;
        }
        
        /* Correction spécifique pour les problèmes d'affichage */
        .card-body [wire\\:submit] {
            display: block !important;
        }
    `;
    document.head.appendChild(style);
</script>
@endpush
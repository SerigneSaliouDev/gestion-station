@extends('layouts.admin')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="container-fluid">
    <!-- En-tête avec bouton d'ajout -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users text-primary"></i> Gestion des Utilisateurs
        </h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus"></i> Nouvel Utilisateur
        </a>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-control">
                        <option value="">Tous les rôles</option>
                        <option value="administrateur" {{ request('role') == 'administrateur' ? 'selected' : '' }}>Administrateur</option>
                        <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="chief" {{ request('role') == 'chief' ? 'selected' : '' }}>Chef des opérations</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Utilisateur</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-control">
                        <option value="">Tous</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nom, email, téléphone..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Liste des Utilisateurs ({{ $users->total() ?? 0 }})
            </h6>
            <div class="text-muted small">
                @php
                    $firstItem = $users->firstItem() ?? 0;
                    $lastItem = $users->lastItem() ?? 0;
                    $total = $users->total() ?? 0;
                @endphp
                Affichage {{ $firstItem }}-{{ $lastItem }} sur {{ $total }}
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Station</th>
                            <th>Statut</th>
                            <th>Dernière connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        @php
                            // DÉTERMINATION DU RÔLE - CORRIGÉ
                            $roleName = 'user'; // Rôle par défaut
                            
                            // 1. Essayer Spatie d'abord (méthode getRoleNames)
                            if (method_exists($user, 'getRoleNames')) {
                                $spatieRoles = $user->getRoleNames();
                                if ($spatieRoles->isNotEmpty()) {
                                    $roleName = $spatieRoles->first();
                                }
                            }
                            
                            // 2. Fallback sur la propriété 'role' du modèle si Spatie n'a rien donné
                            if (($roleName == 'user' || empty($roleName)) && isset($user->role) && !empty($user->role)) {
                                $roleName = $user->role;
                            }
                            
                            // 3. Dernier fallback: utiliser le premier rôle disponible dans la collection roles
                            if (($roleName == 'user' || empty($roleName)) && isset($user->roles) && $user->roles->isNotEmpty()) {
                                $roleName = $user->roles->first()->name ?? 'user';
                            }
                            
                            // Normaliser le nom du rôle (minuscules, sans accents)
                            $roleName = strtolower(trim($roleName));
                            
                            // Configuration des rôles avec leurs affichages et couleurs
                            $roleConfig = [
                                'administrateur' => [
                                    'display' => 'Administrateur',
                                    'color' => 'danger',
                                    'icon' => 'fas fa-crown',
                                    'badge_color' => 'danger'
                                ],
                                'admin' => [
                                    'display' => 'Administrateur',
                                    'color' => 'danger',
                                    'icon' => 'fas fa-crown',
                                    'badge_color' => 'danger'
                                ],
                                'manager' => [
                                    'display' => 'Manager',
                                    'color' => 'info',
                                    'icon' => 'fas fa-user-tie',
                                    'badge_color' => 'info'
                                ],
                                'chief' => [
                                    'display' => 'Chef Opérations',
                                    'color' => 'warning',
                                    'icon' => 'fas fa-user-shield',
                                    'badge_color' => 'warning'
                                ],
                                'chef_operations' => [
                                    'display' => 'Chef Opérations',
                                    'color' => 'warning',
                                    'icon' => 'fas fa-user-shield',
                                    'badge_color' => 'warning'
                                ],
                                'chef' => [
                                    'display' => 'Chef Opérations',
                                    'color' => 'warning',
                                    'icon' => 'fas fa-user-shield',
                                    'badge_color' => 'warning'
                                ],
                                'user' => [
                                    'display' => 'Utilisateur',
                                    'color' => 'secondary',
                                    'icon' => 'fas fa-user',
                                    'badge_color' => 'secondary'
                                ],
                                'caissier' => [
                                    'display' => 'Caissier',
                                    'color' => 'primary',
                                    'icon' => 'fas fa-cash-register',
                                    'badge_color' => 'primary'
                                ],
                                'superviseur' => [
                                    'display' => 'Superviseur',
                                    'color' => 'success',
                                    'icon' => 'fas fa-clipboard-check',
                                    'badge_color' => 'success'
                                ],
                            ];
                            
                            // Configuration par défaut si rôle non trouvé
                            $config = $roleConfig[$roleName] ?? [
                                'display' => ucfirst($roleName),
                                'color' => 'primary',
                                'icon' => 'fas fa-user',
                                'badge_color' => 'primary'
                            ];
                            
                            // Couleur pour l'avatar
                            $avatarColor = $config['color'];
                            
                            // Initiales pour l'avatar
                            $initials = strtoupper(substr($user->name, 0, 1));
                            if(str_contains($user->name, ' ')) {
                                $nameParts = explode(' ', $user->name);
                                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
                            }
                            
                            // Déterminer le statut actif/inactif
                            $isActive = false;
                            if (isset($user->is_active)) {
                                $isActive = $user->is_active;
                            } elseif (isset($user->statut)) {
                                $isActive = ($user->statut == 'active' || $user->statut == 'actif');
                            }
                            
                            // Déterminer si l'email est vérifié
                            $emailVerified = isset($user->email_verified_at) && !is_null($user->email_verified_at);
                            
                            // Déterminer la dernière connexion
                            $lastLoginAt = $user->last_login_at ?? null;
                            $lastLoginIp = $user->last_login_ip ?? null;
                            
                            // Formatage de la date de création
                            $createdAt = '';
                            if (isset($user->created_at) && $user->created_at) {
                                try {
                                    $createdAt = $user->created_at->format('d/m/Y');
                                } catch (\Exception $e) {
                                    $createdAt = 'Date inconnue';
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-{{ $avatarColor }}">
                                            <span class="text-white font-weight-bold">{{ $initials }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">
                                            @if(Route::has('admin.users.show'))
                                            <a href="{{ route('admin.users.show', $user) }}" class="text-dark">
                                                {{ $user->name }}
                                            </a>
                                            @else
                                            <span class="text-dark">{{ $user->name }}</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-envelope mr-1"></i>{{ $user->email }}
                                        </div>
                                        @if(isset($user->telephone) && !empty($user->telephone))
                                        <div class="text-muted small">
                                            <i class="fas fa-phone mr-1"></i>{{ $user->telephone }}
                                        </div>
                                        @endif
                                        @if(!empty($createdAt))
                                        <div class="small text-muted">
                                            <i class="fas fa-calendar-alt mr-1"></i>Créé {{ $createdAt }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $config['badge_color'] }} p-2">
                                    <i class="{{ $config['icon'] }} mr-1"></i>
                                    {{ $config['display'] }}
                                </span>
                                @if(!$emailVerified)
                                <div class="small text-warning mt-1">
                                    <i class="fas fa-exclamation-triangle"></i> Email non vérifié
                                </div>
                                @endif
                            </td>
                            <td>
                                @if(isset($user->station) && $user->station)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-gas-pump text-primary mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold">{{ $user->station->nom }}</div>
                                        @if(isset($user->station->code))
                                        <div class="small text-muted">Code: {{ $user->station->code }}</div>
                                        @endif
                                    </div>
                                </div>
                                @else
                                <span class="text-muted">
                                    <i class="fas fa-times-circle text-danger mr-1"></i>
                                    Non assigné
                                </span>
                                @endif
                            </td>
                            <td>
                                @if($isActive)
                                <span class="badge badge-success p-2">
                                    <i class="fas fa-check-circle"></i> Actif
                                </span>
                                @else
                                <span class="badge badge-danger p-2">
                                    <i class="fas fa-times-circle"></i> Inactif
                                </span>
                                @endif
                                
                                @if(isset($user->statut) && $user->statut == 'pending')
                                <div class="small text-warning mt-1">
                                    <i class="fas fa-clock"></i> En attente
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($lastLoginAt)
                                <div class="font-weight-bold text-primary">
                                    @php
                                        try {
                                            echo \Carbon\Carbon::parse($lastLoginAt)->format('d/m/Y H:i');
                                        } catch (\Exception $e) {
                                            echo 'Date invalide';
                                        }
                                    @endphp
                                </div>
                                @if($lastLoginIp)
                                <div class="small text-muted">
                                    <i class="fas fa-globe"></i> IP: {{ $lastLoginIp }}
                                </div>
                                @endif
                                @else
                                <span class="text-muted">
                                    <i class="fas fa-history mr-1"></i>
                                    Jamais connecté
                                </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <!-- Bouton Voir -->
                                    @if(Route::has('admin.users.show'))
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                    
                                    <!-- Bouton Modifier -->
                                    @if(Route::has('admin.users.edit'))
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    
                                    <!-- Bouton Réinitialiser mot de passe -->
                                    @if(Route::has('admin.users.password.reset'))
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning" 
                                            data-toggle="modal" 
                                            data-target="#resetPasswordModal{{ $user->id }}"
                                            title="Réinitialiser le mot de passe">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    @endif
                                    
                                    <!-- Bouton Supprimer (caché pour soi-même) -->
                                    @if(Route::has('admin.users.destroy') && isset(auth()->user()->id) && $user->id !== auth()->user()->id)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            data-toggle="modal" 
                                            data-target="#deleteUserModal{{ $user->id }}"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>

                                <!-- Modal Réinitialisation Mot de Passe -->
                                @if(Route::has('admin.users.password.reset'))
                                <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-key mr-2"></i>
                                                    Réinitialiser le mot de passe
                                                </h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('admin.users.password.reset', $user) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <p>Voulez-vous réinitialiser le mot de passe de <strong>{{ $user->name }}</strong> ?</p>
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle mr-2"></i>
                                                        Un nouveau mot de passe temporaire sera généré.
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="send_email_{{ $user->id }}">
                                                            <i class="fas fa-envelope mr-2"></i> Envoyer par email ?
                                                        </label>
                                                        <select name="send_email" id="send_email_{{ $user->id }}" class="form-control" required>
                                                            <option value="1">Oui, envoyer par email</option>
                                                            <option value="0">Non, générer seulement</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        <i class="fas fa-times mr-2"></i>Annuler
                                                    </button>
                                                    <button type="submit" class="btn btn-warning">
                                                        <i class="fas fa-key mr-2"></i> Réinitialiser
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Modal Suppression -->
                                @if(Route::has('admin.users.destroy') && isset(auth()->user()->id) && $user->id !== auth()->user()->id)
                                <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    Supprimer l'utilisateur
                                                </h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    <div class="alert alert-danger">
                                                        <i class="fas fa-exclamation-circle fa-2x mb-3 d-block text-center"></i>
                                                        <h6 class="alert-heading text-center font-weight-bold">Attention !</h6>
                                                        <p class="text-center">Cette action est irréversible.</p>
                                                    </div>
                                                    <p class="text-center">Voulez-vous vraiment supprimer l'utilisateur <strong>{{ $user->name }}</strong> ?</p>
                                                    
                                                    <div class="card border-warning mt-3">
                                                        <div class="card-header bg-warning text-white py-2">
                                                            <i class="fas fa-info-circle mr-2"></i> Informations
                                                        </div>
                                                        <div class="card-body py-2">
                                                            <ul class="list-unstyled mb-0">
                                                                <li><strong>Rôle:</strong> {{ $config['display'] }}</li>
                                                                @if(isset($user->station) && $user->station)
                                                                <li><strong>Station:</strong> {{ $user->station->nom }}</li>
                                                                @endif
                                                                @if(!empty($createdAt))
                                                                <li><strong>Créé le:</strong> {{ $createdAt }}</li>
                                                                @endif
                                                                @if($lastLoginAt)
                                                                <li><strong>Dernière connexion:</strong> 
                                                                    @php
                                                                        try {
                                                                            echo \Carbon\Carbon::parse($lastLoginAt)->format('d/m/Y H:i');
                                                                        } catch (\Exception $e) {
                                                                            echo 'Date inconnue';
                                                                        }
                                                                    @endphp
                                                                </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        <i class="fas fa-times mr-2"></i>Annuler
                                                    </button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fas fa-trash mr-2"></i> Supprimer définitivement
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-4x mb-3"></i>
                                    <h5>Aucun utilisateur trouvé</h5>
                                    <p class="mb-2">Essayez de modifier vos critères de recherche</p>
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-sync-alt mr-2"></i>Réinitialiser les filtres
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($users) && $users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
                <div class="text-muted mb-3 mb-md-0">
                    @php
                        $firstItem = $users->firstItem() ?? (($users->currentPage() - 1) * $users->perPage() + 1);
                        $lastItem = $users->lastItem() ?? min($users->currentPage() * $users->perPage(), $users->total());
                        $total = $users->total();
                    @endphp
                    Affichage de {{ $firstItem }} à {{ $lastItem }} sur {{ $total }} utilisateurs
                </div>
                <div>
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
            @elseif(isset($users) && $users->count() > 0)
            <div class="text-center text-muted mt-4">
                Affichage de {{ $users->count() }} utilisateur(s)
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Script pour les boutons de pagination Bootstrap -->
@section('scripts')
<script>
    $(document).ready(function() {
        // Initialisation des tooltips
        $('[title]').tooltip();
        
        // Gestion des modals Bootstrap
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('[autofocus]').focus();
        });

        // Soumission automatique quand les selects changent (optionnel)
        $('#auto-submit').change(function() {
            $(this).closest('form').submit();
        });
        
        // Empêcher la soumission multiple des formulaires
        $('form').on('submit', function() {
            $(this).find('button[type="submit"]').prop('disabled', true);
        });
    });
</script>
@endsection
@endsection

@push('styles')
<style>
    .icon-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
    }
    
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    /* Couleurs de fond pour les avatars */
    .bg-danger { background-color: #e74a3b !important; }
    .bg-info { background-color: #36b9cc !important; }
    .bg-warning { background-color: #f6c23e !important; }
    .bg-secondary { background-color: #858796 !important; }
    .bg-primary { background-color: #4e73df !important; }
    .bg-success { background-color: #1cc88a !important; }
    
    /* Animation pour les modals */
    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
        transform: translate(0, -50px);
    }
    .modal.show .modal-dialog {
        transform: none;
    }
    
    /* Style pour le texte dans les badges */
    .badge i {
        margin-right: 4px;
    }
    
    /* Responsive table */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.85rem;
        }
        .btn-group-sm > .btn {
            padding: 0.2rem 0.4rem;
        }
        .icon-circle {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Version vanilla JavaScript si vous n'utilisez pas jQuery
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation manuelle des tooltips si Bootstrap est chargé
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // Gestion des modals
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            modal.addEventListener('shown.bs.modal', function() {
                var autofocus = this.querySelector('[autofocus]');
                if (autofocus) autofocus.focus();
            });
        });
        
        // Empêcher double soumission
        var forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            form.addEventListener('submit', function() {
                var submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;
            });
        });
    });
</script>
@endpush
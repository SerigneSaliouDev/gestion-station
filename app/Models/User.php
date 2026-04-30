<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Session;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'station_id',
        'active_station_id',
        'telephone',
        'statut',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relations
    
    public function station()
    {
        return $this->belongsTo(Station::class);
    }
    
    public function activeStationRelation()
    {
        return $this->belongsTo(Station::class, 'active_station_id');
    }

    public function stations() 
    {
        if ($this->hasRole('manager')) {
            return $this->station(); 
        }
        
        if ($this->hasRole('chief') || $this->hasRole('administrateur')) {
            return Station::query(); 
        }
        
        return Station::whereRaw('1 = 0');
    }

    public function shifts()
    {
        return $this->hasMany(ShiftSaisie::class);
    }

    // --- LOGIQUE DE RÔLE (Adaptée à Spatie) ---

    public function isChief()
    {
        return $this->hasRole('chief');
    }

    public function isManager()
    {
        return $this->hasRole('manager');
    }

    public function isAdmin()
    {
        return $this->hasRole('administrateur');
    }

    public function isChargeOperations()
    {
        return $this->hasRole('charge-operations');
    }

    public function isPompiste()
    {
        return $this->hasRole('pompiste');
    }

    // --- NOUVELLES MÉTHODES POUR LES RÔLES ---

    /**
     * Obtenir le nom du rôle principal de l'utilisateur
     */
    public function getRoleName()
    {
        if ($this->roles->isNotEmpty()) {
            return $this->roles->first()->name;
        }
        
        return 'user'; // Rôle par défaut
    }

    /**
     * Obtenir le nom du rôle formaté pour l'affichage
     */
    public function getRoleDisplayName()
    {
        $role = $this->getRoleName();
        
        $displayNames = [
            'administrateur' => 'Administrateur',
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'gerant' => 'Gérant',
            'chief' => 'Chef des Opérations',
            'charge-operations' => 'Chargé d\'Opérations',
            'charge_operations' => 'Chargé d\'Opérations',
            'pompiste' => 'Pompiste',
            'user' => 'Utilisateur',
        ];
        
        return $displayNames[$role] ?? ucfirst(str_replace(['-', '_'], ' ', $role));
    }

    /**
     * Obtenir la couleur du badge selon le rôle
     */
    public function getRoleBadgeColor()
    {
        $role = $this->getRoleName();
        
        $colors = [
            'administrateur' => 'danger',
            'admin' => 'danger',
            'manager' => 'warning',
            'gerant' => 'warning',
            'chief' => 'info',
            'charge-operations' => 'primary',
            'charge_operations' => 'primary',
            'pompiste' => 'success',
            'user' => 'secondary',
        ];
        
        return $colors[$role] ?? 'secondary';
    }

    /**
     * Obtenir l'icône selon le rôle
     */
    public function getRoleIcon()
    {
        $role = $this->getRoleName();
        
        $icons = [
            'administrateur' => 'fas fa-user-shield',
            'admin' => 'fas fa-user-shield',
            'manager' => 'fas fa-user-tie',
            'gerant' => 'fas fa-user-tie',
            'chief' => 'fas fa-user-cog',
            'charge-operations' => 'fas fa-user-check',
            'charge_operations' => 'fas fa-user-check',
            'pompiste' => 'fas fa-gas-pump',
            'user' => 'fas fa-user',
        ];
        
        return $icons[$role] ?? 'fas fa-user';
    }

    /**
     * Obtenir la couleur du badge selon le statut
     */
    public function getStatusBadgeColor()
    {
        if ($this->statut === 'active' || $this->is_active) {
            return 'success';
        }
        
        if ($this->statut === 'inactive') {
            return 'danger';
        }
        
        if ($this->statut === 'pending') {
            return 'warning';
        }
        
        return 'secondary';
    }

    /**
     * Obtenir le nom du statut formaté
     */
    public function getStatusDisplayName()
    {
        if ($this->statut === 'active' || $this->is_active) {
            return 'Actif';
        }
        
        if ($this->statut === 'inactive') {
            return 'Inactif';
        }
        
        if ($this->statut === 'pending') {
            return 'En attente';
        }
        
        return $this->statut ?? 'Inconnu';
    }

    /**
     * Vérifier si l'utilisateur est actif
     */
    public function isActive()
    {
        return $this->statut === 'active' || $this->is_active;
    }

    /**
     * Obtenir l'initial du nom pour les avatars
     */
    public function getInitial()
    {
        return strtoupper(substr($this->name, 0, 1));
    }

    /**
     * Obtenir la couleur d'avatar selon le nom
     */
    public function getAvatarColor()
    {
        $colors = ['primary', 'success', 'info', 'warning', 'danger', 'dark'];
        $index = ord($this->getInitial()) % count($colors);
        return $colors[$index];
    }

    /**
     * Obtenir le format d'affichage du téléphone
     */
    public function getFormattedPhone()
    {
        if (!$this->telephone) {
            return 'Non défini';
        }
        
        // Format simple pour les numéros de téléphone
        $phone = preg_replace('/\D/', '', $this->telephone);
        
        if (strlen($phone) === 9) {
            return preg_replace('/(\d{2})(\d{3})(\d{2})(\d{2})/', '$1 $2 $3 $4', $phone);
        }
        
        return $this->telephone;
    }

    /**
     * Obtenir le nombre de jours depuis la création du compte
     */
    public function getDaysSinceCreation()
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Obtenir la date de dernière connexion formatée
     */
    public function getLastLoginFormatted()
    {
        if (!$this->last_login_at) {
            return 'Jamais connecté';
        }
        
        if ($this->last_login_at->isToday()) {
            return 'Aujourd\'hui à ' . $this->last_login_at->format('H:i');
        }
        
        if ($this->last_login_at->isYesterday()) {
            return 'Hier à ' . $this->last_login_at->format('H:i');
        }
        
        return $this->last_login_at->format('d/m/Y à H:i');
    }

    // --- LOGIQUE DE SÉLECTION DE STATION ACTIVE ---

    public function setActiveStation(int $stationId): void
    {
        if ($this->isManager()) {
            $this->station_id = $stationId;
        }
        
        $this->active_station_id = $stationId;
        $this->save();
        
        Session::put('active_station_id', $stationId); 
    }

    public function getActiveStation(): ?int
    {
        if ($this->isManager()) {
            return $this->station_id;
        }
        
        return $this->active_station_id ?? Session::get('active_station_id');
    }

    public function getActiveStationModel(): ?Station
    {
        $stationId = $this->getActiveStation();
        
        return $stationId ? Station::find($stationId) : null;
    }

    public function hasAccessToStation(int $stationId): bool
    {
        if ($this->isChief() || $this->isAdmin()) {
            return true;
        }
        
        return $this->isManager() && $this->station_id === $stationId;
    }

    // --- ACCESSEURS ATTRIBUTES ---

    public function getNomStationAttribute()
    {
        return $this->getActiveStationModel() ? $this->getActiveStationModel()->nom : 'Non sélectionnée';
    }

    public function getCodeStationAttribute()
    {
        return $this->getActiveStationModel() ? $this->getActiveStationModel()->code : null;
    }

    /**
     * Accesseur pour le nom complet avec rôle
     */
    public function getNameWithRoleAttribute()
    {
        return $this->name . ' (' . $this->getRoleDisplayName() . ')';
    }

    /**
     * Accesseur pour l'email masqué
     */
    public function getMaskedEmailAttribute()
    {
        if (!$this->email) {
            return null;
        }
        
        $parts = explode('@', $this->email);
        if (count($parts) !== 2) {
            return $this->email;
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        if (strlen($username) <= 2) {
            $maskedUsername = $username;
        } else {
            $maskedUsername = substr($username, 0, 2) . '***' . substr($username, -1);
        }
        
        return $maskedUsername . '@' . $domain;
    }

    /**
     * Scope pour filtrer les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->where('statut', 'active')
              ->orWhere('is_active', true);
        });
    }

    /**
     * Scope pour filtrer par rôle
     */
    public function scopeByRole($query, $roleName)
    {
        return $query->whereHas('roles', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope pour filtrer les utilisateurs avec station
     */
    public function scopeHasStation($query)
    {
        return $query->whereNotNull('station_id');
    }

    /**
     * Scope pour filtrer les utilisateurs sans station
     */
    public function scopeWithoutStation($query)
    {
        return $query->whereNull('station_id');
    }

    /**
     * Scope pour rechercher des utilisateurs
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('email', 'LIKE', "%{$searchTerm}%")
              ->orWhere('telephone', 'LIKE', "%{$searchTerm}%");
        });
    }

    /**
     * Méthode pour obtenir tous les rôles disponibles formatés pour un select
     */
    public static function getAvailableRoles()
    {
        return [
            'administrateur' => 'Administrateur',
            'manager' => 'Manager',
            'chief' => 'Chef des Opérations',
            'charge-operations' => 'Chargé d\'Opérations',
            'pompiste' => 'Pompiste',
        ];
    }

    /**
     * Méthode pour obtenir toutes les couleurs de badge de rôle
     */
    public static function getRoleBadgeColors()
    {
        return [
            'administrateur' => 'danger',
            'manager' => 'warning',
            'chief' => 'info',
            'charge-operations' => 'primary',
            'pompiste' => 'success',
        ];
    }

    /**
     * Vérifier si l'utilisateur peut modifier un autre utilisateur
     */
    public function canEditUser(User $otherUser)
    {
        // Un admin peut modifier tout le monde
        if ($this->isAdmin()) {
            return true;
        }
        
        // Un chief ne peut pas modifier les admins
        if ($this->isChief() && $otherUser->isAdmin()) {
            return false;
        }
        
        // Un manager ne peut modifier que les utilisateurs de sa station
        if ($this->isManager()) {
            return $otherUser->station_id === $this->station_id && !$otherUser->isAdmin();
        }
        
        return false;
    }

    /**
     * Vérifier si l'utilisateur peut supprimer un autre utilisateur
     */
    public function canDeleteUser(User $otherUser)
    {
        // Personne ne peut se supprimer soi-même
        if ($this->id === $otherUser->id) {
            return false;
        }
        
        // Un admin peut supprimer tout le monde sauf les autres admins
        if ($this->isAdmin()) {
            return !$otherUser->isAdmin();
        }
        
        // Un chief ne peut supprimer que les managers et pompistes
        if ($this->isChief()) {
            return $otherUser->isManager() || $otherUser->isPompiste();
        }
        
        // Un manager ne peut supprimer que les pompistes de sa station
        if ($this->isManager()) {
            return $otherUser->isPompiste() && $otherUser->station_id === $this->station_id;
        }
        
        return false;
    }
    
public function getLayoutName()
{
    if ($this->role === 'admin') return 'admin-layout';
    if ($this->role === 'gerant') return 'gerant-layout';
    return 'app-layout';
}
}
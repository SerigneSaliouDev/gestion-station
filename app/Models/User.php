<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Session; // Importation pour la gestion potentielle de la session

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        // 'role', // ⚠️ Laisser si vous utilisez toujours le champ simple 'role'
        'station_id',
        'active_station_id', // ⬅️ NOUVEAU : ID de la station de travail actuelle (pour Chief/Admin)
        'telephone',
        'statut',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relations
    
    // Relation avec la station d'affectation permanente
    public function station()
    {
        return $this->belongsTo(Station::class);
    }
    
    // Relation avec la station active (si vous voulez une relation dédiée)
    public function activeStationRelation()
    {
        return $this->belongsTo(Station::class, 'active_station_id');
    }

    // Relation avec les stations auxquelles l'utilisateur a accès (pour Chief/Admin)
    // NOTE : Vous devez avoir défini une relation many-to-many (ex: belongsToMany) 
    // ou une portée globale si Chief/Admin voit tout.
 public function stations() 
{
    // Si l'utilisateur est un Manager, on retourne l'objet Relation "station"
    // Laravel peut ensuite appliquer withCount sur cette relation BelongsTo/HasOne.
    if ($this->hasRole('manager')) {
        return $this->station(); 
    }
    
    // Pour les Chiefs/Admins, on retourne le Query Builder de toutes les Stations.
    // Cela permet au contrôleur d'appliquer ->withCount(...) avant ->get().
    if ($this->hasRole('chief') || $this->hasRole('administrateur')) {
        return Station::query(); 
    }
    
    // Fallback : renvoie un Query Builder qui ne retourne rien si l'utilisateur n'a pas de rôle défini
    return Station::whereRaw('1 = 0'); // Retourne un Query Builder vide par sécurité
}

    public function shifts()
    {
        return $this->hasMany(ShiftSaisie::class);
    }

    // --- LOGIQUE DE RÔLE (Adaptée à Spatie) ---

    public function isChief()
    {
        // Utilisez la méthode Spatie pour plus de robustesse
        return $this->hasRole('chief');
    }

    public function isManager()
    {
        return $this->hasRole('manager');
    }

    public function isAdmin()
    {
        return $this->hasRole('administrateur'); // Assurez-vous que le nom du rôle est exact
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique (utilise la méthode Spatie si possible)
     * Garder cette fonction uniquement si vous n'avez pas confiance dans le trait HasRoles
     */
    // public function hasRole($role)
    // {
    //     return parent::hasRole($role); // Utilise la méthode native HasRoles
    // }
    
    // --- LOGIQUE DE SÉLECTION DE STATION ACTIVE ---

    /**
     * Définit la station active de l'utilisateur.
     * Cette méthode est appelée par StationController@selectStation
     */
    public function setActiveStation(int $stationId): void
    {
        // Les Managers utilisent leur station_id permanent, mais pour l'homogénéité du contrôleur, 
        // on peut stocker l'ID dans 'active_station_id'.
        
        if ($this->isManager()) {
            // Un Manager n'a qu'une seule station: on s'assure que la station_id et l'active_station_id sont alignées
            $this->station_id = $stationId;
        }
        
        $this->active_station_id = $stationId;
        $this->save();
        
        // Optionnel : s'assurer que la session reflète la station active
        Session::put('active_station_id', $stationId); 
    }

    /**
     * Récupère l'ID de la station active, en priorisant l'affectation permanente pour le Manager.
     */
    public function getActiveStation(): ?int
    {
        if ($this->isManager()) {
            // Le Manager utilise TOUJOURS sa station permanente
            return $this->station_id;
        }
        
        // Pour les Chiefs/Admins, utilise l'ID sélectionné (ou la session/cache si 'active_station_id' est null)
        return $this->active_station_id ?? Session::get('active_station_id');
    }

    /**
     * Récupère le modèle Station actif.
     */
    public function getActiveStationModel(): ?Station
    {
        $stationId = $this->getActiveStation();
        
        return $stationId ? Station::find($stationId) : null;
    }

    /**
     * Vérifie si l'utilisateur a accès à une station spécifique (pour StationController@selectStation)
     */
    public function hasAccessToStation(int $stationId): bool
    {
        if ($this->isChief() || $this->isAdmin()) {
            // Les Chiefs/Admins ont accès à tout, ou vous vérifiez la relation many-to-many ici
            return true;
        }
        
        // Le Manager n'a accès qu'à sa station permanente
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
}
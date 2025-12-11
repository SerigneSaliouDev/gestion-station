<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSaisie extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_shift',
        'shift',
        'responsable',
        'total_litres',
        'total_ventes',
        'versement',
        'ecart',
        'total_depenses',
        'ecart_final',
        'user_id',
        'station_id', 
        'statut', 
        'validated_by', 
        'validation_date', 
        'notes_validation', 
    ];

    protected $casts = [
        'date_shift' => 'date',
        'total_litres' => 'decimal:2',
        'total_ventes' => 'decimal:2',
        'versement' => 'decimal:2',
        'ecart' => 'decimal:2',
        'total_depenses' => 'decimal:2',
        'ecart_final' => 'decimal:2',
        'validation_date' => 'datetime',
    ];

    // --- RELATIONS ---
    
    public function pompeDetails()
    {
        return $this->hasMany(ShiftPompeDetail::class, 'shift_saisie_id');
    }

    public function depenses()
    {
        return $this->hasMany(Depense::class, 'shift_saisie_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // --- ACCESSEURS DE CALCUL BRUT ---

    public function getEcartInitialCalculatedAttribute()
    {
        // Écart initial = Versement - Ventes
        return $this->versement - $this->total_ventes;
    }

    public function getEcartFinalCalculatedAttribute()
    {
        // Écart final = Versement - (Ventes - Dépenses)
        // Il est souvent calculé comme Ecart Initial + Dépenses.
        return $this->versement - ($this->total_ventes - $this->total_depenses);
    }

    // --- ACCESSEURS DE FORMATAGE (POUR LA VUE) ---
    
    /**
     * Accesseur pour l'écart initial formaté (utilisé par $shift->ecart_formatted).
     */
    public function getEcartFormattedAttribute()
    {
        // Utilise la colonne de la DB pour l'écart initial
        $ecart = $this->ecart; 
        
        if (is_null($ecart)) {
            $ecart = 0.00;
        }

        $montant = abs($ecart);
        $signe = '';

        if ($ecart > 0) {
            $classe = 'success';
            $texte = 'Excédent (trop versé).';
            $signe = '+';
        } elseif ($ecart < 0) {
            $classe = 'danger';
            $texte = 'Déficit (manque à la caisse).';
            $signe = '-';
        } else {
            $classe = 'muted';
            $texte = 'Équilibré (Montant exact).';
            $signe = '';
        }

        return [
            'montant' => $montant,
            'signe' => $signe,
            'classe' => $classe,
            'texte' => $texte,
        ];
    }

    /**
     * Accesseur pour l'écart final formaté (utilisé par $shift->ecart_final_formatted).
     */
    public function getEcartFinalFormattedAttribute()
    {
        // 🎯 CORRECTION: Utilise l'accéder de calcul brut pour l'écart final
        $ecart = $this->ecart_final_calculated; 
        
        if (is_null($ecart)) {
            $ecart = 0.00;
        }

        $montant = abs($ecart);
        $signe = '';

        if ($ecart > 0) {
            $classe = 'success';
            $texte = 'Excédent final.';
            $signe = '+';
        } elseif ($ecart < 0) {
            $classe = 'danger';
            $texte = 'Déficit final.';
            $signe = '-';
        } else {
            $classe = 'muted';
            $texte = 'Écart final nul.';
            $signe = '';
        }

        return [
            'montant' => $montant,
            'signe' => $signe,
            'classe' => $classe,
            'texte' => $texte,
        ];
    }

    // --- ACCESSEURS DE STATUT ---
    
    public function getStatutEcartAttribute()
    {
        $ecart = $this->ecart_final_calculated;
        if ($ecart < 0) {
            return 'manquant';
        } elseif ($ecart > 0) {
            return 'excédent';
        } else {
            return 'équilibré';
        }
    }

    public function getStatutBadgeAttribute()
    {
        switch ($this->statut) {
            case 'valide':
                return '<span class="badge badge-success">Validé</span>';
            case 'rejete':
                return '<span class="badge badge-danger">Rejeté</span>';
            default:
                return '<span class="badge badge-warning">En attente</span>';
        }
    }
    
    // --- MÉTHODES DE VÉRIFICATION ---
    
    public function estEnAttente()
    {
        return $this->statut === 'en_attente';
    }

    public function estValide()
    {
        return $this->statut === 'valide';
    }

    public function estRejete()
    {
        return $this->statut === 'rejete';
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $fillable = [
        'shift_saisie_id',
        'type_depense',
        'montant',
        'description',
        'justificatif',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    /**
     * Relation avec la saisie
     */
    public function shiftSaisie(): BelongsTo
    {
        return $this->belongsTo(ShiftSaisie::class);
    }

    /**
     * ACCESSORS améliorés
     */
    
    // Méthode 1 : Accessor standard
    public function getJustificatifFilePathAttribute()
    {
        return $this->attributes['justificatif'] ?? $this->justificatif;
    }
    
    // Méthode 2 : Accessor avec fallback
    public function getJustificatifFileNameAttribute()
    {
        $justificatif = $this->attributes['justificatif'] ?? $this->justificatif;
        return $justificatif ? basename($justificatif) : null;
    }
    
    // Méthode 3 : Accessor avec fallback
    public function getJustificatifFileExtensionAttribute()
    {
        $justificatif = $this->attributes['justificatif'] ?? $this->justificatif;
        return $justificatif ? pathinfo($justificatif, PATHINFO_EXTENSION) : null;
    }
    
    /**
     * MUTATORS pour assurer la cohérence
     */
    public function setJustificatifFilePathAttribute($value)
    {
        $this->attributes['justificatif'] = $value;
    }
    
    /**
     * Méthode de test pour vérifier l'accès
     */
    public function testAccessors()
    {
        return [
            'justificatif' => $this->justificatif,
            'file_path' => $this->justificatif_file_path,
            'file_name' => $this->justificatif_file_name,
            'extension' => $this->justificatif_file_extension,
        ];
    }
}
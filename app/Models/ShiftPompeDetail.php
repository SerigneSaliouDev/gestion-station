<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftPompeDetail extends Model
{
    protected $table = 'shift_pompe_details';
    
    protected $fillable = [
        'shift_saisie_id',
        'pompe_nom',
        'carburant',
        'prix_unitaire',
        'index_ouverture',
        'index_fermeture',
        'retour_litres',
        'litrage_vendu',
        'montant_ventes',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'index_ouverture' => 'decimal:2',
        'index_fermeture' => 'decimal:2',
        'retour_litres' => 'decimal:2',
        'litrage_vendu' => 'decimal:2',
        'montant_ventes' => 'decimal:2',
    ];

    public function shiftSaisie()
    {
        return $this->belongsTo(ShiftSaisie::class, 'shift_saisie_id');
    }
}

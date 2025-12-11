<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftDepense extends Model
{
    use HasFactory;
    
    protected $table = 'shift_depenses';
    
    protected $fillable = [
        'shift_saisie_id',
        'type_depense',
        'montant',
        'description',
        'justificatif'
    ];
    
    public function shiftSaisie()
    {
        return $this->belongsTo(ShiftSaisie::class);
    }
    
    

}
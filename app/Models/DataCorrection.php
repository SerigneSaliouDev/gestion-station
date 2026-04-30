<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCorrection extends Model
{
    protected $fillable = [
        'correction_type', 'record_id', 'original_values',
        'corrected_values', 'reason', 'corrected_by', 'corrected_at'
    ];
    
    protected $casts = [
        'original_values' => 'array',
        'corrected_values' => 'array',
        'corrected_at' => 'datetime',
    ];
    
    public function corrector()
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}


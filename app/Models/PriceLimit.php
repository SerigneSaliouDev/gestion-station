<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceLimit extends Model
{
    protected $fillable = [
        'fuel_type', 'min_price', 'max_price',
        'effective_from', 'effective_to', 'created_by'
    ];
    
    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Scope pour les limites actives
    public function scopeActive($query)
    {
        return $query->where('effective_from', '<=', now())
            ->where(function($q) {
                $q->where('effective_to', '>=', now())
                  ->orWhereNull('effective_to');
            });
    }
}


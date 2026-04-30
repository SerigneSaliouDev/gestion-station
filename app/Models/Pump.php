<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pump extends Model
{
    protected $fillable = [
        'station_id', 'pump_number', 'fuel_type', 
        'nozzle_count', 'is_active', 'notes'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}

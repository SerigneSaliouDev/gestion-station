<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'scheduled_date',
        'start_date',
        'end_date',
        'assigned_to',
        'estimated_cost',
        'actual_cost',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2'
    ];

    // Relations
    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high')->orWhere('priority', 'critical');
    }

    public function scopeForStation($query, $stationId)
    {
        return $query->where('station_id', $stationId);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_type', 'user_id', 'event', 'description',
        'old_values', 'new_values', 'url', 'ip_address', 'user_agent'
    ];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
    
    public function user()
    {
        return $this->morphTo();
    }
    
    // Helper pour logger les activités
    public static function logActivity($event, $description, $oldValues = null, $newValues = null)
    {
        if (auth()->check()) {
            return self::create([
                'user_type' => get_class(auth()->user()),
                'user_id' => auth()->id(),
                'event' => $event,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}

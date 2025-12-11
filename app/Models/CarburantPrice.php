<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarburantPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_carburant',
        'prix_unitaire',
        'user_id'
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Récupérer le prix actuel d'un carburant
    public static function getCurrentPrice($typeCarburant)
    {
        return self::where('type_carburant', $typeCarburant)
            ->latest()
            ->first();
    }

    // Historique des prix d'un carburant
    public static function getPriceHistory($typeCarburant, $limit = 10)
    {
        return self::where('type_carburant', $typeCarburant)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
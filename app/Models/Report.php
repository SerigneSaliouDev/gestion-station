<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'periode',
        'jours',
        'start_date',
        'end_date',
        'stats',
        'by_fuel',
        'depenses_par_type',
        'ecarts_journaliers',
        'user_id'
    ];

    protected $casts = [
        'stats' => 'array',
        'by_fuel' => 'array',
        'depenses_par_type' => 'array',
        'ecarts_journaliers' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relation avec l'utilisateur qui a généré le rapport
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accesseurs pour faciliter l'accès aux stats
    public function getTotalShiftsAttribute()
    {
        return $this->stats['totalShifts'] ?? 0;
    }

    public function getTotalLitresAttribute()
    {
        return $this->stats['totalLitres'] ?? 0;
    }

    public function getTotalVentesAttribute()
    {
        return $this->stats['totalVentes'] ?? 0;
    }

    public function getTotalEcartFinalAttribute()
    {
        return $this->stats['totalEcartFinal'] ?? 0;
    }

    // Scopes pour faciliter les requêtes
    public function scopeForPeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereDate('start_date', '>=', $startDate)
                    ->whereDate('end_date', '<=', $endDate);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Méthode pour formater la période
    public function getPeriodeDisplayAttribute()
    {
        switch($this->periode) {
            case 'daily':
                return 'Aujourd\'hui';
            case 'weekly':
                return '7 derniers jours';
            case 'monthly':
                return '30 derniers jours';
            case 'custom':
                return $this->jours ? $this->jours . ' derniers jours' : 'Personnalisé';
            default:
                return ucfirst($this->periode);
        }
    }
}
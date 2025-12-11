<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_date',
        'fuel_type',
        'quantity',
        'unit_price',
        'total_amount',
        'pump_number',
        'payment_method',
        'customer_type',
        'customer_name',
        'vehicle_number',
        'shift_id',
        'station_id',
        'recorded_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'stock_before',
        'stock_after',
        'notes'
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2'
    ];

    public function shift()
    {
        return $this->belongsTo(ShiftSaisie::class, 'shift_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Enregistrer une vente et mettre à jour le stock
     */
    public static function recordSale($data)
    {
        \DB::beginTransaction();
        
        try {
            // 1. Récupérer le stock actuel
            $stockBefore = StockMovement::currentStock($data['fuel_type']);
            
            // 2. Vérifier si le stock est suffisant
            if ($stockBefore < $data['quantity']) {
                throw new \Exception("Stock insuffisant. Stock disponible: {$stockBefore} L, Quantité demandée: {$data['quantity']} L");
            }
            
            // 3. Calculer le stock après
            $stockAfter = $stockBefore - $data['quantity'];
            
            // 4. Enregistrer le mouvement de stock
            $movement = StockMovement::create([
                'movement_date' => $data['sale_date'],
                'fuel_type' => $data['fuel_type'],
                'movement_type' => 'vente',
                'quantity' => -$data['quantity'], // Négatif pour une vente
                'unit_price' => $data['unit_price'],
                'total_amount' => $data['total_amount'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'pump_number' => $data['pump_number'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'customer_type' => $data['customer_type'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'recorded_by' => $data['recorded_by'],
                'shift_id' => $data['shift_id'] ?? null,
                'station_id' => $data['station_id'] ?? null,
                'notes' => $data['notes'] ?? null
            ]);
            
            // 5. Enregistrer la vente
            $sale = self::create([
                'sale_date' => $data['sale_date'],
                'fuel_type' => $data['fuel_type'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'total_amount' => $data['total_amount'],
                'pump_number' => $data['pump_number'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'customer_type' => $data['customer_type'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'shift_id' => $data['shift_id'] ?? null,
                'station_id' => $data['station_id'] ?? null,
                'recorded_by' => $data['recorded_by'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $data['notes'] ?? null
            ]);
            
            \DB::commit();
            
            return [
                'sale' => $sale,
                'movement' => $movement,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter
            ];
            
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
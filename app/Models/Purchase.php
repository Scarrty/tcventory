<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_name',
        'purchased_at',
        'subtotal_amount',
        'shipping_amount',
        'fee_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'notes',
        'request_key',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'subtotal_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}

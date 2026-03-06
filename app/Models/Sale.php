<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'sold_at',
        'gross_amount',
        'shipping_amount',
        'fee_amount',
        'tax_amount',
        'net_amount',
        'currency',
        'notes',
        'request_key',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'datetime',
            'gross_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}

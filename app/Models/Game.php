<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'publisher',
    ];

    public function sets(): HasMany
    {
        return $this->hasMany(Set::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

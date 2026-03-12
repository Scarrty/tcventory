<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'event_type',
        'auditable_type',
        'auditable_id',
        'changes',
        'context',
        'event_hash',
        'previous_hash',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'context' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}

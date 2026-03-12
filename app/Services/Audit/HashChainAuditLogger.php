<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditEvent;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class HashChainAuditLogger
{
    /**
     * @param  array<string, mixed>  $changes
     * @param  array<string, mixed>  $context
     */
    public function log(
        string $eventType,
        Model $auditable,
        array $changes,
        array $context = [],
        ?Authenticatable $actor = null,
        ?CarbonInterface $occurredAt = null,
    ): AuditEvent {
        return DB::transaction(function () use ($eventType, $auditable, $changes, $context, $actor, $occurredAt): AuditEvent {
            $latest = AuditEvent::query()->whereNotNull('event_hash')->latest('id')->lockForUpdate()->first();
            $previousHash = $latest?->event_hash;
            $occurredAtValue = ($occurredAt ?? now())->setMicrosecond(0);

            $hashPayload = [
                'event_type' => $eventType,
                'auditable_type' => $auditable::class,
                'auditable_id' => $auditable->getKey(),
                'changes' => $changes,
                'context' => $context,
                'actor_type' => $actor !== null ? $actor::class : null,
                'actor_id' => $actor?->getAuthIdentifier(),
                'occurred_at' => $occurredAtValue->toIso8601String(),
                'previous_hash' => $previousHash,
            ];

            $canonicalJson = json_encode($this->canonicalize($hashPayload), JSON_THROW_ON_ERROR);
            $eventHash = hash('sha256', $canonicalJson);

            return AuditEvent::query()->create([
                'actor_type' => $actor !== null ? $actor::class : null,
                'actor_id' => $actor?->getAuthIdentifier(),
                'event_type' => $eventType,
                'auditable_type' => $auditable::class,
                'auditable_id' => $auditable->getKey(),
                'changes' => $changes,
                'context' => $context,
                'event_hash' => $eventHash,
                'previous_hash' => $previousHash,
                'occurred_at' => $occurredAtValue,
            ]);
        });
    }

    public function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (Arr::isList($value)) {
            return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }

        return $value;
    }
}

<?php

use App\Models\AuditEvent;
use App\Services\Audit\HashChainAuditLogger;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('audit:verify-chain', function (HashChainAuditLogger $logger): int {
    $events = AuditEvent::query()->whereNotNull('event_hash')->orderBy('id')->get();

    $previousHash = null;

    foreach ($events as $event) {
        $hashPayload = [
            'event_type' => $event->event_type,
            'auditable_type' => $event->auditable_type,
            'auditable_id' => $event->auditable_id,
            'changes' => $event->changes,
            'context' => $event->context,
            'actor_type' => $event->actor_type,
            'actor_id' => $event->actor_id,
            'occurred_at' => $event->occurred_at?->toIso8601String(),
            'previous_hash' => $previousHash,
        ];

        $expectedHash = hash('sha256', json_encode($logger->canonicalize($hashPayload), JSON_THROW_ON_ERROR));

        if ($event->previous_hash !== $previousHash || $event->event_hash !== $expectedHash) {
            $this->error("Audit chain verification failed at event ID {$event->id}.");

            return Command::FAILURE;
        }

        $previousHash = $event->event_hash;
    }

    $this->info('Audit chain verified successfully.');

    return Command::SUCCESS;
})->purpose('Verify continuity and integrity of audit_events hash chain');

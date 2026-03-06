<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('movement_type', 40);
            $table->integer('quantity_delta');
            $table->foreignId('from_storage_location_id')->nullable()->constrained('storage_locations')->nullOnDelete();
            $table->foreignId('to_storage_location_id')->nullable()->constrained('storage_locations')->nullOnDelete();
            $table->string('reason', 120)->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampsTz();

            $table->index(['inventory_item_id', 'occurred_at']);
            $table->index(['movement_type', 'occurred_at']);
        });

        Schema::create('audit_events', function (Blueprint $table): void {
            $table->id();
            $table->string('actor_type', 120)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('event_type', 80);
            $table->string('auditable_type', 120)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('changes')->nullable();
            $table->json('context')->nullable();
            $table->char('event_hash', 64)->nullable();
            $table->char('previous_hash', 64)->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampsTz();

            $table->index(['event_type', 'occurred_at']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['actor_type', 'actor_id']);
            $table->unique('event_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('inventory_movements');
    }
};

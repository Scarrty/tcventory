<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('publisher', 120)->nullable();
            $table->timestampsTz();

            $table->index('name');
        });

        Schema::create('sets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->string('name', 140);
            $table->string('code', 40);
            $table->date('release_date')->nullable();
            $table->timestampsTz();

            $table->unique(['game_id', 'code']);
            $table->index(['game_id', 'release_date']);
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('set_id')->nullable()->constrained('sets')->nullOnDelete();
            $table->string('name', 160);
            $table->string('sku', 80)->nullable()->unique();
            $table->string('product_type', 40);
            $table->string('rarity', 40)->nullable();
            $table->boolean('is_sealed')->default(false);
            $table->timestampsTz();

            $table->index(['game_id', 'product_type']);
            $table->index(['set_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('sets');
        Schema::dropIfExists('games');
    }
};

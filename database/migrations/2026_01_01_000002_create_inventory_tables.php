<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 40)->default('other');
            $table->string('code', 60)->nullable()->unique();
            $table->string('description', 255)->nullable();
            $table->timestampsTz();

            $table->index(['type', 'name']);
        });

        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('storage_location_id')->nullable()->constrained('storage_locations')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition', 40)->default('unknown');
            $table->string('grading_provider', 80)->nullable();
            $table->string('grade', 40)->nullable();
            $table->timestampTz('acquired_at')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestampsTz();

            $table->index(['product_id', 'condition']);
            $table->index(['storage_location_id', 'acquired_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('storage_locations');
    }
};

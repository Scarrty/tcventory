<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table): void {
            $table->id();
            $table->string('vendor_name', 140)->nullable();
            $table->timestampTz('purchased_at');
            $table->decimal('subtotal_amount', 14, 2)->default(0);
            $table->decimal('shipping_amount', 14, 2)->default(0);
            $table->decimal('fee_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index(['purchased_at', 'currency']);
        });

        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_cost_amount', 14, 2);
            $table->decimal('line_total_amount', 14, 2);
            $table->timestampsTz();

            $table->index(['purchase_id', 'product_id']);
        });

        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->string('channel', 60)->nullable();
            $table->timestampTz('sold_at');
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('shipping_amount', 14, 2)->default(0);
            $table->decimal('fee_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index(['sold_at', 'currency']);
        });

        Schema::create('sale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price_amount', 14, 2);
            $table->decimal('line_total_amount', 14, 2);
            $table->timestampsTz();

            $table->index(['sale_id', 'product_id']);
        });

        Schema::create('valuations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('value_amount', 14, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('source', 60)->default('manual');
            $table->timestampTz('valued_at');
            $table->timestampsTz();

            $table->index(['inventory_item_id', 'valued_at']);
            $table->index(['source', 'valued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuations');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};

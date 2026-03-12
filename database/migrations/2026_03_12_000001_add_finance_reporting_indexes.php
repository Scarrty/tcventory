<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->index(['channel', 'sold_at'], 'sales_channel_sold_at_index');
        });

        Schema::table('purchases', function (Blueprint $table): void {
            $table->index('purchased_at', 'purchases_purchased_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropIndex('purchases_purchased_at_index');
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropIndex('sales_channel_sold_at_index');
        });
    }
};

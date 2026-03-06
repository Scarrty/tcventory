<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->softDeletesTz();
        });

        Schema::table('sets', function (Blueprint $table): void {
            $table->softDeletesTz();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->softDeletesTz();
        });

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('sets', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('games', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};

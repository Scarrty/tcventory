<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->string('request_key', 120)->nullable()->after('notes');
            $table->unique('request_key');
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->string('request_key', 120)->nullable()->after('notes');
            $table->unique('request_key');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->dropUnique(['request_key']);
            $table->dropColumn('request_key');
        });

        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropUnique(['request_key']);
            $table->dropColumn('request_key');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('bulk_discount_campaigns')) {
            throw new RuntimeException('V3 bulk discount module must be installed first.');
        }
        Schema::table('bulk_discount_campaigns', function (Blueprint $table) {
            $table->unsignedInteger('round_step')->default(0);
            $table->string('round_mode', 12)->default('nearest');
        });
    }

    public function down(): void
    {
        // Explicit rollback only; never run automatically during upgrade.
        Schema::table('bulk_discount_campaigns', function (Blueprint $table) {
            $table->dropColumn(['round_step', 'round_mode']);
        });
    }
};

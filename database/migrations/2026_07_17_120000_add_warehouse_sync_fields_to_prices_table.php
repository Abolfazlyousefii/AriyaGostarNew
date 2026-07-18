<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->string('external_stock_code')->nullable()->after('stock')->index();
            $table->boolean('stock_sync_enabled')->default(false)->after('external_stock_code')->index();
            $table->timestamp('stock_synced_at')->nullable()->after('stock_sync_enabled');
            $table->text('stock_sync_error')->nullable()->after('stock_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->dropIndex(['external_stock_code']);
            $table->dropIndex(['stock_sync_enabled']);
            $table->dropColumn([
                'external_stock_code',
                'stock_sync_enabled',
                'stock_synced_at',
                'stock_sync_error',
            ]);
        });
    }
};

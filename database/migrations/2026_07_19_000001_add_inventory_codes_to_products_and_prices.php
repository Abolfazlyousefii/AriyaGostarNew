<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code', 64)->nullable()->after('id');
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->string('stock_code', 64)->nullable()->after('id');
            $table->boolean('stock_sync_enabled')->default(true)->after('stock_code');
            $table->timestamp('stock_synced_at')->nullable()->after('stock_sync_enabled');
            $table->text('stock_sync_error')->nullable()->after('stock_synced_at');
        });

        DB::table('products')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($products) {
                foreach ($products as $product) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->whereNull('product_code')
                        ->update([
                            'product_code' => 'ARY-P-' . str_pad((string) $product->id, 8, '0', STR_PAD_LEFT),
                        ]);
                }
            });

        DB::table('prices')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($prices) {
                foreach ($prices as $price) {
                    DB::table('prices')
                        ->where('id', $price->id)
                        ->whereNull('stock_code')
                        ->update([
                            'stock_code' => 'ARY-V-' . str_pad((string) $price->id, 10, '0', STR_PAD_LEFT),
                        ]);
                }
            });

        Schema::table('products', function (Blueprint $table) {
            $table->unique('product_code', 'products_product_code_unique');
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->unique('stock_code', 'prices_stock_code_unique');
            $table->index(['stock_sync_enabled', 'stock_code'], 'prices_stock_sync_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->dropIndex('prices_stock_sync_lookup_index');
            $table->dropUnique('prices_stock_code_unique');
            $table->dropColumn([
                'stock_code',
                'stock_sync_enabled',
                'stock_synced_at',
                'stock_sync_error',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_product_code_unique');
            $table->dropColumn('product_code');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->unique()->after('product_code');
            }
            if (!Schema::hasColumn('products', 'video_url')) {
                $table->text('video_url')->nullable()->after('image_alt');
            }
            if (!Schema::hasColumn('products', 'video_cover')) {
                $table->string('video_cover')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('products', 'stock_alert')) {
                $table->unsignedInteger('stock_alert')->nullable()->default(0)->after('unit');
            }
            if (!Schema::hasColumn('products', 'is_rechargeable')) {
                $table->boolean('is_rechargeable')->default(false)->after('stock_alert');
            }
        });

        Schema::table('prices', function (Blueprint $table) {
            if (!Schema::hasColumn('prices', 'barcode')) {
                $table->string('barcode')->nullable()->after('stock_code');
            }
            if (!Schema::hasColumn('prices', 'purchase_price')) {
                $table->decimal('purchase_price', 20, 2)->nullable()->default(0)->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $columns = array_filter(['barcode', 'purchase_price'], fn ($column) => Schema::hasColumn('prices', $column));
            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $columns = array_filter(['barcode', 'video_url', 'video_cover', 'stock_alert', 'is_rechargeable'], fn ($column) => Schema::hasColumn('products', $column));
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};

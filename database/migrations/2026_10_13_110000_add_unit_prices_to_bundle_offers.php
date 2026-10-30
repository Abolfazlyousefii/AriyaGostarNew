<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bundle_offers', function (Blueprint $table) {
            $table->unsignedBigInteger('regular_unit_price')->nullable(); // Toman; NULL = old pack without reference price
            $table->unsignedBigInteger('bundle_unit_price')->nullable();  // Toman; fixed_price is authoritative total
        });
    }

    public function down(): void
    {
        Schema::table('bundle_offers', function (Blueprint $table) {
            $table->dropColumn(['regular_unit_price', 'bundle_unit_price']);
        });
    }
};

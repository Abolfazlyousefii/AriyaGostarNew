<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bundle_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->unsignedInteger('required_quantity');
            $table->unsignedBigInteger('fixed_price'); // Toman, same unit as Cart::finalPrice.
            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at');
            $table->timestamps();
        });

        Schema::create('bundle_offer_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bundle_offer_id');
            $table->unsignedBigInteger('price_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('minimum_if_selected')->default(1);
            $table->boolean('required')->default(false);
            $table->unique(['bundle_offer_id', 'price_id'], 'bundle_variant_unique');
            $table->index(['product_id', 'price_id']);
            $table->foreign('bundle_offer_id')->references('id')->on('bundle_offers')->cascadeOnDelete();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_offer_variants');
        Schema::dropIfExists('bundle_offers');
    }
};

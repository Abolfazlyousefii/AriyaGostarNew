<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bulk_discount_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('discount_type', 20); // percent | fixed_price
            $table->unsignedBigInteger('discount_value'); // percent or final TOMAN
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['enabled', 'starts_at', 'ends_at'], 'bulk_dc_active_idx');
        });

        Schema::create('bulk_discount_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('bulk_discount_campaigns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->boolean('timer_override')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            // A product can belong to one bulk campaign at a time. No stacking.
            $table->unique('product_id', 'bulk_di_product_unique');
            $table->index('campaign_id', 'bulk_di_campaign_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_discount_items');
        Schema::dropIfExists('bulk_discount_campaigns');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_controls', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id')->unique();

            // Global sales allocation. Null means unlimited.
            $table->boolean('enabled')->default(true)->index();
            $table->unsignedInteger('sale_limit')->nullable();
            $table->timestamp('counter_started_at')->nullable()->index();

            // Timed campaign discount.
            $table->boolean('discount_enabled')->default(false);
            $table->string('discount_type', 20)->nullable();
            $table->unsignedBigInteger('discount_value')->nullable();
            $table->timestamp('discount_starts_at')->nullable();
            $table->timestamp('discount_ends_at')->nullable();

            // Discount showcase.
            $table->boolean('show_in_discount_box')->default(false)->index();
            $table->unsignedInteger('discount_box_limit')->nullable();
            $table->unsignedInteger('discount_box_order')->default(100);

            // New-arrivals showcase.
            $table->boolean('show_in_new_arrivals')->default(false)->index();
            $table->unsignedInteger('new_arrivals_limit')->nullable();
            $table->unsignedInteger('new_arrivals_order')->default(100);

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->index(['show_in_discount_box', 'discount_box_order']);
            $table->index(['show_in_new_arrivals', 'new_arrivals_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_controls');
    }
};
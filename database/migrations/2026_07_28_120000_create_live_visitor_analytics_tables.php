<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_visitors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('visitor_token', 64)->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 120)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 40)->nullable();
            $table->string('device_name', 120)->nullable();
            $table->string('platform', 120)->nullable();
            $table->string('browser', 120)->nullable();
            $table->string('country', 120)->nullable();
            $table->string('region', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('timezone', 120)->nullable();
            $table->text('current_url')->nullable();
            $table->string('current_path', 1024)->nullable();
            $table->string('current_title')->nullable();
            $table->text('referrer')->nullable();
            $table->unsignedBigInteger('current_product_id')->nullable();
            $table->string('current_product_title')->nullable();
            $table->unsignedBigInteger('current_category_id')->nullable();
            $table->string('current_category_title')->nullable();
            $table->string('search_term')->nullable();
            $table->unsignedInteger('cart_items_count')->default(0);
            $table->decimal('cart_total', 20, 2)->default(0);
            $table->unsignedTinyInteger('intent_score')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('last_seen_at');
            $table->index(['user_id', 'last_seen_at']);
            $table->index(['current_product_id', 'last_seen_at'], 'lv_product_seen_idx');
            $table->index(['cart_items_count', 'last_seen_at'], 'lv_cart_seen_idx');
        });

        Schema::create('live_visitor_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('live_visitor_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event_type', 50);
            $table->text('url')->nullable();
            $table->string('path', 1024)->nullable();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_title')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('category_title')->nullable();
            $table->string('search_term')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->foreign('live_visitor_id')->references('id')->on('live_visitors')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['event_type', 'occurred_at']);
            $table->index(['product_id', 'occurred_at']);
            $table->index(['search_term', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_visitor_events');
        Schema::dropIfExists('live_visitors');
    }
};

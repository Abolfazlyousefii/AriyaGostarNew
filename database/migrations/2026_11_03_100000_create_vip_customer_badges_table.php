<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vip_customer_badges', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('icon_type', 20)->default('premium_star');
            $table->string('emoji', 32)->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_customer_badges');
    }
};

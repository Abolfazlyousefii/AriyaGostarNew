<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('manual_order_payment_confirmations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('confirmed_by');
            $table->unsignedBigInteger('amount_toman');
            $table->string('receipt_reference', 150);
            $table->text('payment_note')->nullable();
            $table->timestamp('confirmed_at');
            $table->timestamps();
            $table->index('confirmed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_order_payment_confirmations');
    }
};

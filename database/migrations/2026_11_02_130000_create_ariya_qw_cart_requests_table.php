<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ariya_qw_cart_requests', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->uuid('request_id');
            $table->char('payload_hash', 64);
            $table->text('summary_json');
            $table->timestamps();
            $table->unique(['cart_id', 'request_id'], 'ariya_qw_cart_request_unique');
            $table->index('created_at', 'ariya_qw_cart_request_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ariya_qw_cart_requests');
    }
};

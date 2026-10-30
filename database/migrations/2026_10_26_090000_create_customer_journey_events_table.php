<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_journey_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('kind', 24); // request, browser
            $table->string('event', 40);
            $table->string('request_id', 32)->nullable();
            $table->string('page_id', 32)->nullable();
            $table->string('method', 8)->nullable();
            $table->string('route_name', 120)->nullable();
            $table->string('path', 200)->nullable(); // pathname only; no query string
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('error_class', 80)->nullable();
            $table->string('source_file', 100)->nullable();
            $table->unsignedInteger('source_line')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
            $table->index(['request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_journey_events');
    }
};

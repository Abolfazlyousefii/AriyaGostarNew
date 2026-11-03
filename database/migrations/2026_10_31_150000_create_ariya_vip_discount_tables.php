<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vip_discount_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->decimal('percent', 5, 2)->default(0);
            $table->timestamps();
        });
        DB::table('vip_discount_settings')->insert([
            'id' => 1, 'percent' => 0, 'created_at' => now(), 'updated_at' => now()
        ]);
        Schema::create('vip_customer_memberships', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('vip_customer_memberships');
        Schema::dropIfExists('vip_discount_settings');
    }
};

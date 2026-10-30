<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAriyaDirectDiscountModesToPrices extends Migration
{
    public function up()
    {
        Schema::table('prices',function (Blueprint $table) {
            $table->string('direct_discount_mode',20)->nullable();
            $table->unsignedBigInteger('direct_discount_value')->nullable();
        });
    }
    public function down()
    {
        Schema::table('prices',function (Blueprint $table) {
            $table->dropColumn(['direct_discount_mode','direct_discount_value']);
        });
    }
}

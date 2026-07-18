<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('inventory_document_type')->nullable()->after('shipping_status');
            $table->string('inventory_document_id')->nullable()->after('inventory_document_type');
            $table->uuid('inventory_document_uuid')->nullable()->after('inventory_document_id');
            $table->string('inventory_sync_status')->nullable()->index()->after('inventory_document_uuid');
            $table->timestamp('inventory_synced_at')->nullable()->after('inventory_sync_status');
            $table->text('inventory_last_error')->nullable()->after('inventory_synced_at');
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->string('external_variant_id')->nullable()->index()->after('id');
            $table->string('variant_code')->nullable()->index()->after('external_variant_id');
            $table->timestamp('inventory_updated_at')->nullable()->after('stock_sync_error');
            $table->unsignedBigInteger('inventory_version')->nullable()->after('inventory_updated_at');
            $table->boolean('inventory_disabled')->default(false)->after('inventory_version');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['inventory_document_type','inventory_document_id','inventory_document_uuid','inventory_sync_status','inventory_synced_at','inventory_last_error']);
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn(['external_variant_id','variant_code','inventory_updated_at','inventory_version','inventory_disabled']);
        });
    }
};

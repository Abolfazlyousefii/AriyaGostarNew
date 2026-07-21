<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('categories', 'menu_icon')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('menu_icon')->nullable()->after('image');
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('categories', 'menu_icon')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('menu_icon');
            });
        }
    }
};

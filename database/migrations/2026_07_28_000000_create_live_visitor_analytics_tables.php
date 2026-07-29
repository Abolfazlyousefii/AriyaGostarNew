<?php

use App\Support\LiveVisitorSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        LiveVisitorSchema::ensure();
    }

    public function down(): void
    {
        Schema::dropIfExists('live_visitor_events');
        Schema::dropIfExists('live_visitors');
    }
};

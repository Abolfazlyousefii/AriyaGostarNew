<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LiveVisitorSchema
{
    private static bool $ensured = false;

    /**
     * The module is intentionally self-healing so uploading the files is enough
     * to remove the 500 error. The migration is still included for normal deploys.
     */
    public static function ensure(): void
    {
        if (self::$ensured) {
            return;
        }

        self::$ensured = true;

        if (!Schema::hasTable('live_visitors')) {
            Schema::create('live_visitors', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->uuid('visitor_uuid')->unique();
                $table->string('session_id', 191)->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('phone', 50)->nullable()->index();
                $table->string('ip_address', 64)->nullable()->index();
                $table->text('user_agent')->nullable();

                $table->string('device_type', 30)->nullable()->index();
                $table->string('device_name', 100)->nullable();
                $table->string('platform', 100)->nullable();
                $table->string('browser', 100)->nullable();

                $table->string('country_code', 10)->nullable();
                $table->string('country', 100)->nullable();
                $table->string('region', 100)->nullable();
                $table->string('city', 100)->nullable();

                $table->text('current_url')->nullable();
                $table->string('current_path', 500)->nullable();
                $table->string('current_route', 191)->nullable();
                $table->string('current_page_title', 255)->nullable();
                $table->string('current_action', 255)->nullable();
                $table->unsignedBigInteger('current_product_id')->nullable()->index();
                $table->string('current_product_title', 255)->nullable();
                $table->text('referrer')->nullable();

                $table->timestamp('first_seen_at')->nullable()->index();
                $table->timestamp('last_seen_at')->nullable()->index();
                $table->timestamp('last_activity_at')->nullable()->index();
                $table->unsignedInteger('page_views')->default(0);
                $table->unsignedInteger('cart_items_count')->default(0);
                $table->decimal('cart_total', 18, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('live_visitor_events')) {
            Schema::create('live_visitor_events', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('live_visitor_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('event_type', 50)->default('page_view')->index();
                $table->string('event_name', 191)->nullable();
                $table->text('page_url')->nullable();
                $table->string('page_path', 500)->nullable();
                $table->string('route_name', 191)->nullable();
                $table->string('page_title', 255)->nullable();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('product_title', 255)->nullable();
                $table->string('search_query', 255)->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('occurred_at')->nullable()->index();
                $table->timestamps();

                $table->foreign('live_visitor_id')
                    ->references('id')
                    ->on('live_visitors')
                    ->cascadeOnDelete();
            });
        }

        // Repair an incomplete table left by an older upload.
        self::addMissingVisitorColumns();
        self::addMissingEventColumns();
    }

    private static function addMissingVisitorColumns(): void
    {
        $columns = [
            'visitor_uuid' => fn (Blueprint $t) => $t->uuid('visitor_uuid')->nullable()->unique(),
            'session_id' => fn (Blueprint $t) => $t->string('session_id', 191)->nullable()->index(),
            'user_id' => fn (Blueprint $t) => $t->unsignedBigInteger('user_id')->nullable()->index(),
            'phone' => fn (Blueprint $t) => $t->string('phone', 50)->nullable()->index(),
            'ip_address' => fn (Blueprint $t) => $t->string('ip_address', 64)->nullable()->index(),
            'user_agent' => fn (Blueprint $t) => $t->text('user_agent')->nullable(),
            'device_type' => fn (Blueprint $t) => $t->string('device_type', 30)->nullable()->index(),
            'device_name' => fn (Blueprint $t) => $t->string('device_name', 100)->nullable(),
            'platform' => fn (Blueprint $t) => $t->string('platform', 100)->nullable(),
            'browser' => fn (Blueprint $t) => $t->string('browser', 100)->nullable(),
            'country_code' => fn (Blueprint $t) => $t->string('country_code', 10)->nullable(),
            'country' => fn (Blueprint $t) => $t->string('country', 100)->nullable(),
            'region' => fn (Blueprint $t) => $t->string('region', 100)->nullable(),
            'city' => fn (Blueprint $t) => $t->string('city', 100)->nullable(),
            'current_url' => fn (Blueprint $t) => $t->text('current_url')->nullable(),
            'current_path' => fn (Blueprint $t) => $t->string('current_path', 500)->nullable(),
            'current_route' => fn (Blueprint $t) => $t->string('current_route', 191)->nullable(),
            'current_page_title' => fn (Blueprint $t) => $t->string('current_page_title', 255)->nullable(),
            'current_action' => fn (Blueprint $t) => $t->string('current_action', 255)->nullable(),
            'current_product_id' => fn (Blueprint $t) => $t->unsignedBigInteger('current_product_id')->nullable()->index(),
            'current_product_title' => fn (Blueprint $t) => $t->string('current_product_title', 255)->nullable(),
            'referrer' => fn (Blueprint $t) => $t->text('referrer')->nullable(),
            'first_seen_at' => fn (Blueprint $t) => $t->timestamp('first_seen_at')->nullable()->index(),
            'last_seen_at' => fn (Blueprint $t) => $t->timestamp('last_seen_at')->nullable()->index(),
            'last_activity_at' => fn (Blueprint $t) => $t->timestamp('last_activity_at')->nullable()->index(),
            'page_views' => fn (Blueprint $t) => $t->unsignedInteger('page_views')->default(0),
            'cart_items_count' => fn (Blueprint $t) => $t->unsignedInteger('cart_items_count')->default(0),
            'cart_total' => fn (Blueprint $t) => $t->decimal('cart_total', 18, 2)->default(0),
            'metadata' => fn (Blueprint $t) => $t->json('metadata')->nullable(),
            'created_at' => fn (Blueprint $t) => $t->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $t) => $t->timestamp('updated_at')->nullable(),
        ];

        self::addMissingColumns('live_visitors', $columns);
    }

    private static function addMissingEventColumns(): void
    {
        $columns = [
            'live_visitor_id' => fn (Blueprint $t) => $t->unsignedBigInteger('live_visitor_id')->nullable()->index(),
            'user_id' => fn (Blueprint $t) => $t->unsignedBigInteger('user_id')->nullable()->index(),
            'event_type' => fn (Blueprint $t) => $t->string('event_type', 50)->default('page_view')->index(),
            'event_name' => fn (Blueprint $t) => $t->string('event_name', 191)->nullable(),
            'page_url' => fn (Blueprint $t) => $t->text('page_url')->nullable(),
            'page_path' => fn (Blueprint $t) => $t->string('page_path', 500)->nullable(),
            'route_name' => fn (Blueprint $t) => $t->string('route_name', 191)->nullable(),
            'page_title' => fn (Blueprint $t) => $t->string('page_title', 255)->nullable(),
            'product_id' => fn (Blueprint $t) => $t->unsignedBigInteger('product_id')->nullable()->index(),
            'product_title' => fn (Blueprint $t) => $t->string('product_title', 255)->nullable(),
            'search_query' => fn (Blueprint $t) => $t->string('search_query', 255)->nullable(),
            'payload' => fn (Blueprint $t) => $t->json('payload')->nullable(),
            'occurred_at' => fn (Blueprint $t) => $t->timestamp('occurred_at')->nullable()->index(),
            'created_at' => fn (Blueprint $t) => $t->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $t) => $t->timestamp('updated_at')->nullable(),
        ];

        self::addMissingColumns('live_visitor_events', $columns);
    }

    /** @param array<string, callable(Blueprint): void> $columns */
    private static function addMissingColumns(string $table, array $columns): void
    {
        foreach ($columns as $column => $definition) {
            try {
                if (!Schema::hasColumn($table, $column)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($definition): void {
                        $definition($blueprint);
                    });
                }
            } catch (Throwable $exception) {
                // Do not make the storefront unavailable because an optional
                // analytics column could not be added. The controller will show
                // a clear error only inside the admin page if the core table fails.
                report($exception);
            }
        }
    }
}

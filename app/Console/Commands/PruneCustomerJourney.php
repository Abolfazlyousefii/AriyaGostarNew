<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneCustomerJourney extends Command
{
    protected $signature = 'customer-journey:prune {--dry-run : Show count without deleting}';
    protected $description = 'Delete customer journey telemetry older than retention period';

    public function handle(): int
    {
        $days = max(1, (int) config('customer_journey.retention_days', 30));
        $cutoff = now()->subDays($days);
        $query = DB::table('customer_journey_events')->where('created_at', '<', $cutoff);
        if ($this->option('dry-run')) {
            $this->info('Old records: '.$query->count());
            return 0;
        }
        $deleted = 0;
        do {
            $ids = DB::table('customer_journey_events')->where('created_at', '<', $cutoff)->orderBy('id')->limit(1000)->pluck('id');
            $n = $ids->isEmpty() ? 0 : DB::table('customer_journey_events')->whereIn('id', $ids)->delete();
            $deleted += $n;
        } while ($n > 0);
        $this->info('Deleted records: '.$deleted);
        return 0;
    }
}

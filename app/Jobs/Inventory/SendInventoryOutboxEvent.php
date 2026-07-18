<?php

namespace App\Jobs\Inventory;

use App\Models\Inventory\InventoryOutboxEvent;
use App\Models\Order;
use App\Services\Inventory\InventorySignature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendInventoryOutboxEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $afterCommit = true;
    public int $timeout = 30;

    public function __construct(public int $outboxEventId) {}
    public function tries(): int { return (int) config('inventory.max_attempts', 10); }
    public function backoff(): array { return [60, 300, 900, 1800, 3600]; }

    public function handle(): void
    {
        $event = InventoryOutboxEvent::findOrFail($this->outboxEventId);
        if (in_array($event->status, ['sent', 'permanently_failed'], true)) return;
        if (!config('inventory.enabled')) return;
        $body = json_encode($event->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $timestamp = now()->toIso8601String();
        $response = Http::withOptions(['verify' => (bool) config('inventory.verify_ssl')])
            ->connectTimeout((int) config('inventory.connect_timeout', 5))
            ->timeout((int) config('inventory.timeout', 15))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Ariya-Event-Id' => $event->event_id,
                'X-Ariya-Timestamp' => $timestamp,
                'X-Ariya-Signature' => InventorySignature::sign($timestamp, $body),
                'X-Ariya-Source' => 'ariya-site',
            ])->withBody($body, 'application/json')->post(rtrim(config('inventory.base_url'), '/') . '/api/integrations/ariya-site/v1/events');

        $event->increment('attempts');
        if ($response->successful()) {
            $data = $response->json() ?: [];
            $event->update(['status'=>'sent','sent_at'=>now(),'last_error'=>null]);
            Order::whereKey($event->aggregate_id)->update([
                'inventory_document_type' => $data['document_type'] ?? $data['inventory_document_type'] ?? null,
                'inventory_document_id' => $data['document_id'] ?? $data['inventory_document_id'] ?? null,
                'inventory_document_uuid' => $data['document_uuid'] ?? $data['inventory_document_uuid'] ?? null,
                'inventory_sync_status' => 'accepted',
                'inventory_synced_at' => now(),
                'inventory_last_error' => null,
            ]);
            return;
        }
        $permanent = $response->status() >= 400 && $response->status() < 500 && $response->status() !== 429;
        $event->update(['status'=>$permanent?'permanently_failed':'pending','last_error'=>'HTTP '.$response->status().': '.substr($response->body(),0,1000),'next_attempt_at'=>now()->addMinutes(5)]);
        Order::whereKey($event->aggregate_id)->update(['inventory_sync_status'=>$permanent?'failed':'pending','inventory_last_error'=>$event->last_error]);
        if (!$permanent) $this->release(300);
    }

    public function failed(Throwable $e): void
    {
        if ($event = InventoryOutboxEvent::find($this->outboxEventId)) {
            $event->update(['status'=>'failed','last_error'=>$e->getMessage(),'next_attempt_at'=>now()->addMinutes(15)]);
        }
        Log::error('Inventory outbox failed', ['event_id' => optional($event)->event_id, 'error' => $e->getMessage()]);
    }
}

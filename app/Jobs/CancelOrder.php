<?php

namespace App\Jobs;

use App\Events\ProductPricesChanged;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->order && $this->order->status == 'unpaid'
            && ! in_array((string) $this->order->inventory_sync_status,
                ['credit_processing', 'credit_failed', 'synced'], true)
            && ! $this->order->inventory_document_id) {
            if ($this->order->inventory_stock_hold_id) {
                // Do not mark canceled if central Inventory cannot release the hold. Retry safely.
                app(\App\Services\Inventory\InventoryStockHoldClient::class)->release($this->order);
            }
            $this->order->update([
                'status' => 'canceled',
                'inventory_stock_hold_status' => $this->order->inventory_stock_hold_id ? 'released' : null,
            ]);

            if (!$this->order->inventory_stock_hold_id) {
                foreach ($this->order->items as $item) {
                    $price = $item->get_price;
                    if ($price) {
                        $price->update(['stock' => $price->stock + $item->quantity]);
                        event(new ProductPricesChanged($price->product));
                    }
                }
            }

            foreach ($this->order->reservedOrders()->get() as $reserved_order) {
                $reserved_order->update([
                    'reserve' => true
                ]);
            }

            $this->order->reservedOrders()->detach();
        }
    }
}

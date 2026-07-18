<?php

namespace App\Http\Controllers\Api\Integrations\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryInboundEvent;
use App\Services\Inventory\CatalogEventProcessor;
use App\Services\Inventory\InventorySignature;
use Illuminate\Http\Request;

class InventoryEventController extends Controller
{
    public function __invoke(Request $request)
    {
        $allowed = config('inventory.allowed_ips', []);
        if ($allowed && !in_array($request->ip(), $allowed, true)) return response()->json(['message'=>'Forbidden'], 403);
        $eventId = $request->header('X-Ariya-Event-Id');
        $timestamp = $request->header('X-Ariya-Timestamp');
        $signature = $request->header('X-Ariya-Signature');
        if (!$eventId || !$timestamp || !$signature || !InventorySignature::valid($timestamp, $request->getContent(), $signature)) {
            return response()->json(['message'=>'Invalid signature'], 401);
        }
        $data = $request->json()->all();
        $type = $data['event_type'] ?? null;
        if (!in_array($type, CatalogEventProcessor::TYPES, true)) return response()->json(['message'=>'Unsupported event_type'], 422);
        $hash = hash('sha256', $request->getContent());
        $existing = InventoryInboundEvent::where('event_id', $eventId)->first();
        if ($existing) {
            if ($existing->payload_hash !== $hash) return response()->json(['message'=>'Event payload conflict'], 409);
            return response()->json(['status'=>'already_processed','event_status'=>$existing->status]);
        }
        $event = InventoryInboundEvent::create(['event_id'=>$eventId,'source'=>$request->header('X-Ariya-Source','inventory'),'event_type'=>$type,'payload_hash'=>$hash,'payload'=>$data,'status'=>'pending']);
        app(CatalogEventProcessor::class)->process($event);
        return response()->json(['status'=>$event->fresh()->status]);
    }
}

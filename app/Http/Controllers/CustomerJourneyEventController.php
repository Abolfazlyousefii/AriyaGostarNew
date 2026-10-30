<?php

namespace App\Http\Controllers;

use App\Support\CustomerJourneySafe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CustomerJourneyEventController extends Controller
{
    public function store(Request $request)
    {
        // Treat client data as untrusted. Only a strict allowlist reaches the database.
        if (!config('customer_journey.enabled') || !config('customer_journey.client_enabled')) {
            return response('', 204);
        }
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);
        $data = $request->validate([
            'event' => 'required|string|max:40',
            'page_id' => 'nullable|string|regex:/^[a-f0-9]{16}$/D',
            'error_class' => 'nullable|string|max:80',
            'source_file' => 'nullable|string|max:500',
            'source_line' => 'nullable|integer|min:1|max:4294967295',
        ]);
        $event = CustomerJourneySafe::event($data['event']);
        if (!$event) return response('', 204);
        try {
            DB::table('customer_journey_events')->insert([
                'user_id' => (int) $user->id,
                'kind' => 'browser',
                'event' => $event,
                'page_id' => $data['page_id'] ?? null,
                'method' => 'JS',
                'path' => CustomerJourneySafe::path($request->headers->get('referer', '/')),
                'error_class' => CustomerJourneySafe::errorClass($data['error_class'] ?? null),
                'source_file' => CustomerJourneySafe::sourceFile($data['source_file'] ?? null),
                'source_line' => $data['source_line'] ?? null,
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            // Best effort; telemetry must not interfere with checkout.
        }
        return response('', 204);
    }
}

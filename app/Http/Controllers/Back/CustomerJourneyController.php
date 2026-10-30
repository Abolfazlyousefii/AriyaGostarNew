<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\CustomerJourneySafe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerJourneyController extends Controller
{
    private function allowed(Request $request, bool $export = false): void
    {
        Gate::authorize('users.index');
        if ($export) Gate::authorize('users.export');
    }

    private function filters(Request $request): array
    {
        $input = $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
        ]);
        if (isset($input['from'], $input['to']) && strtotime($input['to']) - strtotime($input['from']) > 31 * 86400) {
            throw \Illuminate\Validation\ValidationException::withMessages(['to' => 'Maximum report range is 31 days.']);
        }
        $from = $input['from'] ?? now()->subDays(7)->format('Y-m-d');
        $to = $input['to'] ?? now()->format('Y-m-d');
        if (strtotime($to) - strtotime($from) > 31 * 86400) {
            throw \Illuminate\Validation\ValidationException::withMessages(['to' => 'Maximum report range is 31 days.']);
        }
        return ['from' => $from, 'to' => $to];
    }

    private function query(User $user, array $filter)
    {
        return DB::table('customer_journey_events')->where('user_id', $user->id)
            ->where('created_at', '>=', $filter['from'].' 00:00:00')
            ->where('created_at', '<=', $filter['to'].' 23:59:59');
    }

    public function index(Request $request, User $user)
    {
        $this->allowed($request);
        $filter = $this->filters($request);
        $events = $this->query($user, $filter)->orderByDesc('created_at')->orderByDesc('id')->paginate(100)->withQueryString();
        return view('back.customer-journey.index', compact('user', 'events', 'filter'));
    }

    public function download(Request $request, User $user): StreamedResponse
    {
        $this->allowed($request, true);
        $filter = $this->filters($request);
        $filename = sprintf('ariya-customer-%d-journey-%s-to-%s.jsonl', $user->id, $filter['from'], $filter['to']);
        return response()->streamDownload(function () use ($user, $filter) {
            echo json_encode([
                'record_type' => 'export_info',
                'format_version' => 1,
                'customer_id' => (int) $user->id,
                'from' => $filter['from'],
                'to' => $filter['to'],
                'exported_at' => now()->toIso8601String(),
                'notes' => 'No raw payload, cookies, authentication credentials or URL query strings are included. Events prior to activation may be absent.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
            // Streaming constant-memory export, ordered for reconstruction.
            foreach ($this->query($user, $filter)->orderBy('id')->cursor() as $event) {
                echo json_encode([
                    'timestamp' => $event->created_at,
                    'kind' => $event->kind,
                    'event' => $event->event,
                    'path' => CustomerJourneySafe::path($event->path),
                    'route' => $event->route_name,
                    'method' => $event->method,
                    'http_status' => $event->http_status,
                    'duration_ms' => $event->duration_ms,
                    'request_id' => $event->request_id,
                    'page_id' => $event->page_id,
                    'error_class' => $event->error_class,
                    'source_file' => $event->source_file,
                    'source_line' => $event->source_line,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) . "\n";
            }
        }, $filename, [
            'Content-Type' => 'application/x-ndjson; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

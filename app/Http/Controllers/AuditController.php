<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with(['causer', 'subject' => fn ($q) => $q->withoutGlobalScopes()])->latest();

        if ($request->filled('action')) {
            $query->where('description', $request->action);
        }
        if ($request->filled('causer')) {
            $query->where('causer_id', $request->causer);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // ponytail: keyset not required at this scale; offset paginate is fine
        $activities = $query->paginate(20)->withQueryString();

        $actions = Activity::distinct()->pluck('description')->sort()->values();

        return view('audit.index', compact('activities', 'actions'));
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Activity::with(['causer', 'subject' => fn ($q) => $q->withoutGlobalScopes()]);
        if ($request->filled('action')) {
            $query->where('description', $request->action);
        }
        if ($request->filled('causer')) {
            $query->where('causer_id', $request->causer);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // ponytail: stream CSV, no package. Escape fields per RFC 4180.
        $rows = $query->latest()->get();
        $filename = 'audit-log-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['time', 'action', 'causer', 'subject_type', 'subject_id', 'ip', 'user_agent']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->created_at->format('Y-m-d H:i:s'),
                    $r->description,
                    $r->causer->username ?? ($r->properties['identifier'] ?? ''),
                    $r->subject_type ? class_basename($r->subject_type) : '',
                    $r->subject_id ?? '',
                    $r->properties['ip'] ?? '',
                    $r->properties['user_agent'] ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with(['causer', 'subject'])->latest();

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
}

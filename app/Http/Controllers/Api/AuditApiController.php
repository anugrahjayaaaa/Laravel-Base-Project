<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * @group Audit
 *
 * Audit log access (gated by `audit.view`).
 */
class AuditApiController extends Controller
{
    /** List audit entries (filter by ?action=, ?causer=, ?from=, ?to=). */
    public function index(Request $request): JsonResponse
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

        return response()->json(ActivityResource::collection($query->paginate(20))->response()->getData(true));
    }

    /** Available action types (for filter dropdowns). */
    public function actions(): JsonResponse
    {
        $actions = Activity::distinct()->pluck('description')->sort()->values();

        return response()->json(['actions' => $actions]);
    }
}

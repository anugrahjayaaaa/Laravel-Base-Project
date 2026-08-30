<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\View\View;

class BillingAdminController extends Controller
{
    /** Admin billing dashboard: KPIs + recent payments + active licenses. */
    public function index(): View
    {
        $revenue = (float) Payment::where('status', 'paid')->sum('amount');
        $activeSubscribers = User::whereHas('licenses', fn ($q) => $q->active())->count();
        $paidThisMonth = Payment::where('status', 'paid')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        $planBreakdown = License::active()
            ->selectRaw('plan_slug, count(*) as total')
            ->groupBy('plan_slug')
            ->pluck('total', 'plan_slug');

        $payments = Payment::with('user')->latest()->paginate(20);
        $licenses = License::with('user')->active()->latest()->paginate(20);

        return view('admin.billing.index', compact(
            'revenue', 'activeSubscribers', 'paidThisMonth', 'planBreakdown',
            'payments', 'licenses'
        ));
    }
}

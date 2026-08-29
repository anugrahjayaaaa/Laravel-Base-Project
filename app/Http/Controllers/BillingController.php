<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /** User picks a paid plan -> start checkout (dummy mode completes at once). */
    public function checkout(Request $request): RedirectResponse
    {
        $plan = Plan::where('slug', $request->input('plan_slug'))
            ->where('price_monthly', '>', 0)
            ->where('is_active', true)
            ->firstOrFail();

        $payment = BillingService::checkout($plan, auth()->id());

        $key = $payment->status === 'paid' ? 'messages.payment_paid' : 'messages.payment_pending';

        return redirect()->route('plans.index')->with($payment->status === 'paid' ? 'success' : 'error', __($key));
    }

    /** PG webhook (no auth — the gateway calls this). */
    public function webhook(Request $request): JsonResponse
    {
        BillingService::handleWebhook($request->all());

        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\Billing\BillingCancelRequest;
use App\Models\License;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BillingController extends Controller
{
    /** User billing portal: current plan, active license, payment history. */
    public function index(): View
    {
        $user = auth()->user();
        $license = BillingService::userActiveLicense($user);
        $plan = $license ? Plan::where('slug', $license->plan_slug)->first() : null;
        $payments = $user->payments()->latest()->paginate(10);

        return view('billing.index', compact('user', 'license', 'plan', 'payments'));
    }

    /** Cancel the current user's subscription (revoke + freeze). */
    public function cancel(BillingCancelRequest $request): RedirectResponse
    {
        BillingService::cancelUser($request->user());

        return redirect()->route('billing.index')
            ->with('success', __('messages.subscription_canceled'));
    }

    /** Download a dummy invoice PDF for a payment. */
    public function invoice(Payment $payment): Response
    {
        abort_unless($payment->user_id === auth()->id(), 403);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('billing.invoice', compact('payment'));

        return $pdf->download($payment->invoice_no.'.pdf');
    }
}

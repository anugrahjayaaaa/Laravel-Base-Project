<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class NotificationController extends Controller
{
    public function index(): View
    {
        // ponytail: notifications are the recent audit feed; 30 most recent, no read/unread state
        $activities = Activity::with(['causer'])->latest()->limit(30)->get();

        return view('notifications.index', compact('activities'));
    }
}

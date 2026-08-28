<?php

namespace App\Http\Controllers;

use App\Models\NotificationRead;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class NotificationController extends Controller
{
    public function index(): View
    {
        // ponytail: notifications are the recent audit feed; 30 most recent, with per-user read state
        $activities = Activity::with(['causer'])->latest()->limit(30)->get();

        $userId = Auth::id();
        foreach ($activities as $a) {
            NotificationRead::updateOrCreate(
                ['user_id' => $userId, 'activity_id' => $a->id],
                ['read_at' => now()]
            );
        }

        return view('notifications.index', compact('activities'));
    }
}

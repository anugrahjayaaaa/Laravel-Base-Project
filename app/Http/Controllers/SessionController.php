<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function index(): View
    {
        // ponytail: session driver=database -> list from sessions table for current user
        $sessions = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderByDesc('last_activity')
            ->get();

        return view('profile.sessions', compact('sessions'));
    }

    public function logoutOthers(Request $request): RedirectResponse
    {
        // delete other session rows for this user, then regenerate current token
        DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('id', '<>', $request->session()->getId())
            ->delete();

        if ($request->filled('password')) {
            Auth::logoutOtherDevices($request->password);
        }

        return redirect()->route('sessions.index')->with('success', __('messages.sessions_logged_out'));
    }
}

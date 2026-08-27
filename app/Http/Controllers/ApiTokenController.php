<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function index(): View
    {
        $tokens = auth()->user()->tokens()->latest()->get();
        return view('api-tokens.index', [
            'tokens' => $tokens,
            'newToken' => session('new_token'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $plain = auth()->user()->createToken($data['name'], ['mobile'])->plainTextToken;
        // ponytail: show plain token once (no DB retrieval), flash only
        return redirect()->route('api-tokens.index')->with('new_token', $plain);
    }

    public function destroy(int $token): RedirectResponse
    {
        auth()->user()->tokens()->where('id', $token)->delete();
        return redirect()->route('api-tokens.index')->with('status', 'Token revoked.');
    }
}

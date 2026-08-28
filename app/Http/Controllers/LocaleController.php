<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $locale = $request->input('locale');
        abort_unless(in_array($locale, ['en', 'id']), 422, 'Unsupported locale.');

        session(['locale' => $locale]);

        return redirect()->back();
    }
}

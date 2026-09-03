<?php

namespace App\Http\Controllers;

use App\Http\Requests\Locale\LocaleUpdateRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * System-wide settings page (default language + registration toggle).
 *
 * Gated by `feature.manage` permission on the route (see routes/web.php).
 * Stores boolean-ish flags as Setting rows:
 *  - locale_default  : default app locale (en|id)
 *  - registration_enabled : whether /register is accessible
 */
class SettingsController extends Controller
{
    /** Show the system settings page. */
    public function index(): View
    {
        return view('settings.system', [
            'defaultLocale' => Setting::get('locale_default', config('app.locale', 'en')),
            'registrationEnabled' => (bool) Setting::get('registration_enabled', false),
            'availableLocales' => config('app.available_locales', ['en', 'id']),
        ]);
    }

    /** Update system settings (default locale + registration toggle). */
    public function update(Request $request): RedirectResponse
    {
        // ponytail: minimal validation — no FormRequest needed for this simple toggle
        $request->validate([
            'locale_default' => ['required', 'string', 'in:' . implode(',', config('app.available_locales', ['en', 'id']))],
            'registration_enabled' => ['boolean'],
        ]);

        Setting::set('locale_default', $request->input('locale_default'));
        Setting::set('registration_enabled', $request->boolean('registration_enabled'));

        return back()->with('status', __('messages.settings_updated'));
    }
}

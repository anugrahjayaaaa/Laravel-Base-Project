<?php

namespace App\Http\Controllers;

use App\Enums\LicenseMode;
use App\Models\Plan;
use App\Models\Role;
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
            'licenseMode' => Setting::get('license_mode', 'global'),
            'defaultPlan' => Setting::get('default_plan', null),
            'defaultRole' => Setting::get('default_role', null),
            'availableLocales' => config('app.available_locales', ['en', 'id']),
            'plans' => Plan::where('is_active', true)->orderBy('name')->get(['slug', 'name']),
            'roles' => Role::whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Update system settings (default locale + registration toggle). */
    public function update(Request $request): RedirectResponse
    {
        // ponytail: minimal validation — no FormRequest needed for these simple toggles
        $request->validate([
            'locale_default' => ['required', 'string', 'in:' . implode(',', config('app.available_locales', ['en', 'id']))],
            'registration_enabled' => ['boolean'],
            'license_mode' => ['required', 'string', 'in:global,per_user'],
            'default_plan' => ['nullable', 'string', 'exists:plans,slug'],
            'default_role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        Setting::set('locale_default', $request->input('locale_default'));
        Setting::set('registration_enabled', $request->boolean('registration_enabled'));
        Setting::set('license_mode', $request->input('license_mode'));
        Setting::set('default_plan', $request->input('default_plan'));
        Setting::set('default_role', $request->input('default_role'));

        return back()->with('status', __('messages.settings_updated'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseService;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'title' => 'Dashboard',
            'userCount' => User::count(),
            'roleCount' => Role::count(),
            'auditCount' => Activity::count(),
            'licenseStatus' => LicenseService::status(),
            'licenseDaysLeft' => LicenseService::daysLeft(),
            'activePlan' => Setting::get('active_plan', 'free'),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard', [
            'title' => 'Dashboard',
            'userCount' => User::count(),
            'roleCount' => Role::count(),
            'auditCount' => Activity::count(),
            'dbName' => DB::connection()->getDatabaseName(),
        ]);
    }
}

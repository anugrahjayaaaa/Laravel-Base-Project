<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard', [
            'title' => 'Dashboard',
            'userCount' => \App\Models\User::count(),
            'roleCount' => \Spatie\Permission\Models\Role::count(),
            'auditCount' => \Spatie\Activitylog\Models\Activity::count(),
            'dbName' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
        ]);
    }
}

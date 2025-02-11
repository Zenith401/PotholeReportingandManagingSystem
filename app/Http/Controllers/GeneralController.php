<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function Dashboard()
    {
        // Return the 'sAdminDashboard' view
        return view('Dashboard');
    }
}


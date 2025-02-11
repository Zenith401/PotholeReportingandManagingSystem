<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function AdminDashboard()
    {
        // Return the 'sAdminDashboard' view
        return view('/admin/AdminDashboard');
    }
}


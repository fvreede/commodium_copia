<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
        ];

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $stats,
        ]);
    }
}

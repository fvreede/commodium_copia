<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class CustomerDashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('Dashboard', [
            'activeOrders' => $user->orders()
                ->with(['items.product', 'deliverySlot'])
                ->whereIn('status', ['pending', 'processing'])
                ->latest()
                ->get(),
            
            'orderHistory' => $user->orders()
                ->with(['items.product', 'deliverySlot'])
                ->where('status', 'completed')
                ->latest()
                ->paginate(10)
        ]);
    }
}
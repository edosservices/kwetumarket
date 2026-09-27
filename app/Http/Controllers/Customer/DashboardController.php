<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\AreaStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AreaStats $stats): View
    {
        return view('pages.customer.dashboard', [
            'stats' => $stats->customer($request->user()),
            'user' => $request->user(),
        ]);
    }
}

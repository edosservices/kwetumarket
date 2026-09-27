<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\AreaStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AreaStats $stats): View
    {
        return view('pages.admin.dashboard', [
            'stats' => $stats->admin(),
        ]);
    }
}

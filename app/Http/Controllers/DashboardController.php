<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function home(Request $request): View
    {
        return view('pages.dashboard.home', [
            'user' => $request->user(),
        ]);
    }

    public function vendor(Request $request): View
    {
        return view('pages.dashboard.vendor', [
            'user' => $request->user(),
        ]);
    }

    public function delivery(Request $request): View
    {
        return view('pages.dashboard.delivery', [
            'user' => $request->user(),
        ]);
    }

    public function admin(Request $request): View
    {
        return view('pages.dashboard.admin', [
            'user' => $request->user(),
        ]);
    }

    public function profile(Request $request): View
    {
        $this->authorize('update', $request->user());

        return view('pages.dashboard.profile', [
            'user' => $request->user(),
        ]);
    }
}

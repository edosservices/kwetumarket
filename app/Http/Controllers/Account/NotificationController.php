<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('pages.account.notifications', [
            'area' => $user->area()->value,
            'notifications' => $user->notifications()->latest()->limit(50)->get(),
        ]);
    }
}

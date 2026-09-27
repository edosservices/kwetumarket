<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $this->authorize('update', $user);

        return view('pages.account.profile', [
            'user' => $user,
            'area' => $user->area()->value,
        ]);
    }
}

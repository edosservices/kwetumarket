<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserSuspensionController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $this->authorize('suspend', $user);

        $user->forceFill(['suspended_at' => now()])->save();

        AuditLog::record($request->user(), 'users.suspend', $user, [
            'email' => $user->email,
        ]);

        return back();
    }
}

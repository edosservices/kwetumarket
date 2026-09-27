<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\AccountNotice;
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
            'module' => 'users',
        ]);

        $user->notify(new AccountNotice(
            __('ui.notifications.suspended_title'),
            __('ui.notifications.suspended_body'),
        ));

        return back();
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('reactivate', $user);

        abort_unless($user->isSuspended(), 422);

        $user->forceFill(['suspended_at' => null])->save();

        AuditLog::record($request->user(), 'users.reactivate', $user, [
            'email' => $user->email,
            'module' => 'users',
        ]);

        $user->notify(new AccountNotice(
            __('ui.notifications.reactivated_title'),
            __('ui.notifications.reactivated_body'),
        ));

        return back();
    }
}

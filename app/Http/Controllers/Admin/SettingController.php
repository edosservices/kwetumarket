<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.edit'), 403);

        $data = $request->validate([
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9._-]+$/'],
            'value' => ['nullable', 'string', 'max:1000'],
        ]);

        $setting = Setting::query()->updateOrCreate(
            ['key' => $data['key']],
            ['value' => $data['value'] ?? ''],
        );

        AuditLog::record($request->user(), 'settings.edit', $setting, [
            'key' => $setting->key,
        ]);

        return back();
    }
}

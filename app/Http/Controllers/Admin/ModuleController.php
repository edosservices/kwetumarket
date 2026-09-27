<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountArea;
use App\Http\Controllers\Controller;
use App\Services\Rbac\ModuleDirectory;
use App\Support\Rbac\RoleCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __invoke(Request $request, string $module, ModuleDirectory $directory): View
    {
        $payload = $directory->open($request->user(), AccountArea::Admin, $module);

        if ($module === 'roles') {
            return view('pages.admin.access.roles', [
                'title' => $payload['title'],
                'roles' => RoleCatalog::roles(),
                'holders' => $request->user()->isSuperAdmin() ? RoleCatalog::holders() : collect(),
            ]);
        }

        if ($module === 'permissions') {
            return view('pages.admin.access.permissions', [
                'title' => $payload['title'],
                'permissions' => RoleCatalog::permissions(),
                'holders' => $request->user()->isSuperAdmin() ? RoleCatalog::holders() : collect(),
            ]);
        }

        return view('pages.shared.module', [
            ...$payload,
            'area' => 'admin',
        ]);
    }
}

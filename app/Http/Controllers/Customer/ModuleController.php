<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AccountArea;
use App\Http\Controllers\Controller;
use App\Services\Rbac\ModuleDirectory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __invoke(Request $request, string $module, ModuleDirectory $directory): View
    {
        return view('pages.shared.module', [
            ...$directory->open($request->user(), AccountArea::Customer, $module),
            'area' => 'customer',
        ]);
    }
}

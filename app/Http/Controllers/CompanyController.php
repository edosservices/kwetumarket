<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CompanyController extends Controller
{
    public function show(string $page): View
    {
        abort_unless(view()->exists('pages.company.'.$page), 404);

        return view('pages.company.'.$page);
    }
}

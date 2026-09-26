<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CatalogController extends Controller
{
    public function cart(): View
    {
        return view('pages.catalog', [
            'title' => __('ui.catalog.cart_title'),
            'description' => __('ui.catalog.cart_body'),
        ]);
    }
}

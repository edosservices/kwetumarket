<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CatalogController extends Controller
{
    public function categories(): View
    {
        return view('pages.catalog', [
            'title' => __('ui.catalog.categories_title'),
            'description' => __('ui.catalog.categories_body'),
        ]);
    }

    public function shops(): View
    {
        return view('pages.catalog', [
            'title' => __('ui.catalog.shops_title'),
            'description' => __('ui.catalog.shops_body'),
        ]);
    }

    public function cart(): View
    {
        return view('pages.catalog', [
            'title' => __('ui.catalog.cart_title'),
            'description' => __('ui.catalog.cart_body'),
        ]);
    }
}

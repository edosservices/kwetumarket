<?php

namespace App\Http\Controllers;

use App\Contracts\ProductSearch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request, ProductSearch $search): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $query = trim((string) ($validated['q'] ?? ''));
        $results = $search->search($query);

        return view('pages.search', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}

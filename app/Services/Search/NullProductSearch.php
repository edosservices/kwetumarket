<?php

namespace App\Services\Search;

use App\Contracts\ProductSearch;

class NullProductSearch implements ProductSearch
{
    public function search(string $query, array $filters = []): array
    {
        return [
            'items' => [],
            'total' => 0,
        ];
    }
}

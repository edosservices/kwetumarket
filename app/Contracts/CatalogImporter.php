<?php

namespace App\Contracts;

use App\Models\Shop;
use App\Models\User;

interface CatalogImporter
{
    /**
     * @return array{created: int, errors: list<string>}
     */
    public function import(Shop $shop, string $contents, User $actor): array;
}

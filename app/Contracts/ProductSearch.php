<?php

namespace App\Contracts;

interface ProductSearch
{
    /**
     * Recherche catalogue. L'implémentation actuelle est vide ;
     * Meilisearch ou SQL pourront la remplacer sans changer les contrôleurs.
     *
     * @param  array<string, mixed>  $filters
     * @return array{items: array<int, mixed>, total: int}
     */
    public function search(string $query, array $filters = []): array;
}

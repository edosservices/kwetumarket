<?php

namespace App\Livewire;

use Livewire\Component;

class MarketplaceSearch extends Component
{
    public string $query = '';

    public string $variant = 'header';

    public function mount(): void
    {
        $this->query = (string) request()->query('q', '');
    }

    public function search(): void
    {
        $validated = $this->validate([
            'query' => ['nullable', 'string', 'max:120'],
        ]);

        $this->redirect(route('search', ['q' => $validated['query'] ?? '']), navigate: false);
    }

    public function render()
    {
        return view('livewire.marketplace-search');
    }
}

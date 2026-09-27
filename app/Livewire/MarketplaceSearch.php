<?php

namespace App\Livewire;

use App\Enums\CatalogStatus;
use App\Enums\VendorStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MarketplaceSearch extends Component
{
    public string $query = '';

    public string $category = '';

    public string $variant = 'header';

    public function mount(): void
    {
        $this->query = (string) request()->query('q', '');
        $this->category = (string) request()->query('category', '');
    }

    public function search(): void
    {
        $validated = $this->validate([
            'query' => ['nullable', 'string', 'max:120'],
        ]);

        $params = ['q' => $validated['query'] ?? ''];

        if (ctype_digit($this->category)) {
            $params['category'] = (int) $this->category;
        }

        $this->redirect(route('search', $params), navigate: false);
    }

    public function render(): View
    {
        $term = trim($this->query);
        $products = collect();
        $categories = collect();
        $vendors = collect();

        if (mb_strlen($term) >= 2) {
            $like = '%'.$this->escapeLike($term).'%';
            $products = Product::query()->published()->where('name', 'like', $like)->limit(5)->get(['id', 'name', 'slug']);
            $categories = Category::query()->where('status', CatalogStatus::Active)->where('name', 'like', $like)->limit(4)->get(['id', 'name', 'slug']);
            $vendors = Vendor::query()
                ->where('status', VendorStatus::Active)
                ->where(function ($query) use ($like): void {
                    $query->where('business_name', 'like', $like)
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $like));
                })
                ->with(['user:id,name', 'shops' => fn ($shops) => $shops->where('status', 'active')->limit(1)])
                ->limit(4)
                ->get();
        }

        return view('livewire.marketplace-search', [
            'roots' => Category::query()
                ->whereNull('parent_id')
                ->where('status', CatalogStatus::Active)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(24)
                ->get(['id', 'name']),
            'suggestions' => [
                'products' => $products,
                'categories' => $categories,
                'vendors' => $vendors,
            ],
            'suggesting' => mb_strlen($term) >= 2,
        ]);
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}

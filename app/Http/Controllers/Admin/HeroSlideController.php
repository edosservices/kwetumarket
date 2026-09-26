<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Services\Catalog\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeroSlideController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.marketing.slides', [
            'slides' => HeroSlide::query()->orderBy('placement')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request, MediaStorage $media): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = $request->hasFile('image')
            ? $media->store($request->file('image'), 'marketing/hero')
            : null;
        HeroSlide::query()->create($data);

        return back()->with('success', __('experience.slide_saved'));
    }

    public function update(Request $request, HeroSlide $slide, MediaStorage $media): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $media->delete($slide->image);
            $data['image'] = $media->store($request->file('image'), 'marketing/hero');
        }

        $slide->update($data);

        return back()->with('success', __('experience.slide_saved'));
    }

    public function destroy(HeroSlide $slide, MediaStorage $media): RedirectResponse
    {
        $media->delete($slide->image);
        $slide->delete();

        return back()->with('success', __('experience.slide_saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'placement' => ['required', 'in:home,auth'],
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'cta_label' => ['nullable', 'string', 'max:40'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');
        unset($data['image']);

        return $data;
    }
}

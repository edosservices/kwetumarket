<?php

namespace App\Services\Search;

use App\Contracts\ImageRecognitionInterface;
use App\Models\ImageSearch;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageSearchService
{
    public function __construct(
        private SearchImageStorage $storage,
        private ImageRecognitionInterface $recognition,
    ) {}

    public function store(UploadedFile $file, ?User $user): ImageSearch
    {
        $path = $this->storage->store($file);
        $absolute = $this->storage->disk();
        $absolutePath = \Illuminate\Support\Facades\Storage::disk($absolute)->path($path);
        $insight = $this->recognition->analyze($absolutePath);

        return ImageSearch::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user?->id,
            'disk' => $this->storage->disk(),
            'image_path' => $path,
            'provider' => $insight->provider,
            'limited' => $insight->limited,
            'label' => $insight->label,
            'detected_category' => $insight->category,
            'detected_brand' => $insight->brand,
            'detected_text' => $insight->visibleText,
            'detected_attributes' => [
                'subcategory' => $insight->subcategory,
                'model' => $insight->model,
                'color' => $insight->color,
                'shape' => $insight->shape,
                'keywords' => $insight->keywords,
                'traits' => $insight->attributes,
            ],
            'confidence' => $insight->confidence,
            'expires_at' => now()->addHours((int) config('twende.vision.retention_hours')),
        ]);
    }
}

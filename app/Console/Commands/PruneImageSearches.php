<?php

namespace App\Console\Commands;

use App\Models\ImageSearch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneImageSearches extends Command
{
    protected $signature = 'smart-search:prune';

    protected $description = 'Delete expired Twende Smart Search images';

    public function handle(): int
    {
        $deleted = 0;

        ImageSearch::query()
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->each(function (ImageSearch $search) use (&$deleted): void {
                Storage::disk($search->disk)->delete($search->image_path);
                $search->delete();
                $deleted++;
            });

        $this->info($deleted.' expired search image(s) removed.');

        return self::SUCCESS;
    }
}

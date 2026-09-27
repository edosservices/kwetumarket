<?php

use App\Services\Commerce\PointsService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('twende:points-expire', function (PointsService $points): void {
    $this->info((string) $points->expireDue());
})->purpose('Expire loyalty points past their date');

<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Bake with love!');
})->purpose('Display an inspiring quote');

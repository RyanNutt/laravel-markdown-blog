<?php

use Illuminate\Support\Facades\Route;


if (config('mdblog.route')) {
    Route::get('/{slug}', function ($slug) {
        ray($slug);
    });
}

return [];

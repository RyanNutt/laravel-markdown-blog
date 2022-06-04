<?php

use Illuminate\Support\Facades\Route;


if (config('mdblog.route')) {
    // Allow this to be disabled in case there's already a catch-all route in project
    Route::get('/{slug}', function ($slug) {
        ray($slug);
    });
}

Route::get('/' . config('mdblog.permalinks.blog'), config('mdblog.controllers.blog'))
    ->name('mdblog.blog');
Route::get('/' . config('mdblog.permalinks.categories'), config('mdblog.controllers.category'))
    ->name('mdblog.category');
Route::get('/' . config('mdblog.permalinks.tags'), config('mdblog.controllers.tag'))
    ->name('mdblog.tag');

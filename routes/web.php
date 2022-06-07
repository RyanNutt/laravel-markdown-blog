<?php

use Illuminate\Support\Facades\Route;
use Aelora\MarkdownBlog\Models\Post;

// Index Routes
Route::get(config('mdblog.permalinks.blog'), config('mdblog.controllers.blog'))
    ->name('mdblog.blog');
Route::get(config('mdblog.permalinks.categories'), config('mdblog.controllers.category'))
    ->name('mdblog.category');
Route::get(config('mdblog.permalinks.tags'), config('mdblog.controllers.tag'))
    ->name('mdblog.tag');

// Add all permalinks as routes
$posts = Post::all();
if (!empty($posts)) {
    foreach ($posts as $post) {
        Route::get($post->permalink, config('mdblog.controllers.post'));
    }
}

// Webhook route, only add if set
if (config('mdblog.webhook', false) !== false && config('mdblog.webhook.route', false) !== false) {
    Route::post(config('mdblog.webhook.route'), config('mdblog.controllers.webhook'));
}

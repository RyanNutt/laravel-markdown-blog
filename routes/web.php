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

if (config('mdblog.permalinks.sitemap', '/md-sitemap.xml') != false) {
    Route::get(config('mdblog.permalinks.sitemap', '/md-sitemap.xml'), config('mdblog.controllers.sitemap'))
        ->name('mdblog.sitemap');
}

// Add all permalinks as routes
try {
    // In case the database table doesn't exist, there wouldn't be 
    // any routes anyway. 
    $posts = Post::all();
    if (!empty($posts)) {
        foreach ($posts as $post) {
            Route::get($post->permalink, config('mdblog.controllers.post'));
        }
    }
} catch (\Exception $e) {
}

// Webhook route, only add if set
if (config('mdblog.webhook', false) !== false && config('mdblog.webhook.route', false) !== false) {
    Route::post(config('mdblog.webhook.route'), config('mdblog.controllers.webhook'));
}

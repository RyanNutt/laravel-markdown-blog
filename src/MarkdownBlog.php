<?php

namespace Aelora\MarkdownBlog;

use Aelora\MarkdownBlog\Models\Category;
use Aelora\MarkdownBlog\Models\Post;
use Aelora\MarkdownBlog\Models\Posts;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;


class MarkdownBlog
{

    /**
     * Returns the name of the cache store to use for the scanned posts
     */
    public function cacheStore(): string
    {
        $out = config('mdblog.cache');
        if (empty($out) || $out == 'default') {
            return config('cache.default');
        }
        return $out;
    }

    /**
     * Registers routes, should go in /routes/web.php, at the end
     * if using the catch-all route. 
     */
    public function routes(bool $catchAll = true)
    {

        Route::get(config('mdblog.permalinks.blog'), config('mdblog.controllers.blog'))
            ->name('mdblog.blog');
        Route::get(config('mdblog.permalinks.categories'), config('mdblog.controllers.category'))
            ->name('mdblog.category');
        Route::get(config('mdblog.permalinks.tags'), config('mdblog.controllers.tag'))
            ->name('mdblog.tag');
        if ($catchAll) {
            $this->routeCatchAll();
        }
    }

    public function routeCatchAll()
    {
        Route::get('/{slug}', config('mdblog.controllers.post'))
            ->where('slug', '.*')
            ->name('mdblog.post');
    }

    /**
     * Returns the controller for a single post. Used when there is already
     * a catch all route in the app and it needs to be able to hand off
     * to the post controller. 
     */
    public function postController()
    {
        $controllerInfo = explode('@', config('mdblog.controllers.post'));
        ray($controllerInfo);
        return call_user_func([new $controllerInfo[0], $controllerInfo[1]]);
    }
}

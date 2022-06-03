<?php

namespace Aelora\MarkdownBlog;

use Aelora\MarkdownBlog\Models\Category;
use Aelora\MarkdownBlog\Models\Post;
use Aelora\MarkdownBlog\Models\Posts;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

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

    public function posts(): Posts
    {
        return (new \Aelora\MarkdownBlog\Models\Posts());
    }

    public function post(string $permalink): ?Post
    {
        return $this->posts()->where('permalink', $permalink)->first();
    }

    public function categories(): Collection
    {
        return Category::all();
    }
}

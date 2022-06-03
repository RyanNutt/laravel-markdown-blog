<?php

namespace Aelora\MarkdownBlog\Commands;

use Illuminate\Console\Command;
use Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Support\Facades\Cache;
use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Aelora\MarkdownBlog\Models\Category;

class BuildCache extends Command
{
    public $signature = 'mdblog:cache';

    public $description = 'Builds the cache for the Markdown Blog';

    public function handle(): int
    {
        Cache::store(MarkdownBlog::cacheStore())->forget('mdblog.posts');
        Cache::store(MarkdownBlog::cacheStore())->forget('mdblog.categories');

        // Don't care about the data, but this triggers a cache rebuild
        MarkdownBlog::posts();

        return self::SUCCESS;
    }
}

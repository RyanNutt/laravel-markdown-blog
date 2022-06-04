<?php

namespace Aelora\MarkdownBlog\Commands;

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Aelora\MarkdownBlog\Models\Category;
use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Str;
use Symfony\Component\Yaml\Yaml;


class BuildCache extends Command
{
    public $signature = 'mdblog:cache';

    public $description = 'Builds the cache for the Markdown Blog';

    public function handle(): int
    {
        Cache::store(MarkdownBlog::cacheStore())->forget('mdblog.posts');
        Cache::store(MarkdownBlog::cacheStore())->forget('mdblog.categories');
        Cache::store(MarkdownBlog::cacheStore())->forget('mdblog.tags');

        // Load all posts and look for images
        $allPosts = Post::all();
        foreach ($allPosts as $post) {
            // Fix any images so they're pointing to the right place
            $content = $post->content();
            $content = preg_replace_callback('/!\[.*?\]\((.*?)\)/', function ($match) {
                $toReplace = $match[0];
                $imagePath = $match[1];
                if (preg_match('/^https?:\/\//', $imagePath) || Str::startsWith($imagePath, '//') || Str::startsWith($imagePath, 'data:')) {
                    return $toReplace;
                } else if (!file_exists(storage_path('mdblog/' . $imagePath))) {
                    // If the file isn't in the repo, don't bother replacing the url
                    return $toReplace;
                }
                $destPath = public_path(config('mdblog.public_path') . '/' . $imagePath);
                File::makeDirectory(dirname($destPath), 0755, true, true);
                File::copy(storage_path('mdblog/' . $imagePath), $destPath);

                $url = url(config('mdblog.public_path') . '/' . $imagePath);
                return Str::replace($match[1], $url, $match[0]);
            }, $content);

            $post->updateContent($content);
        }

        // Don't care about the data, but this triggers a cache rebuild
        \Aelora\MarkdownBlog\Models\Category::first();
        \Aelora\MarkdownBlog\Models\Tag::first();

        return self::SUCCESS;
    }
}

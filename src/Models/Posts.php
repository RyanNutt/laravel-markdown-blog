<?php

namespace Aelora\MarkdownBlog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Aelora\MarkdownBlog\Facades\MarkdownBlog;

class Posts extends Model
{
    public Collection $posts;

    public function __construct()
    {
        $this->posts = Cache::store(MarkdownBlog::cacheStore())->rememberForever('mdblog.posts', function () {
            $allFiles = File::allFiles(storage_path('mdblog'));
            $files = collect();
            if (!empty($allFiles)) {
                foreach ($allFiles as $file) {
                    $filePath = preg_replace('#^' . storage_path('mdblog') . '#', '', $file->getPathname());
                    $files->push(Post::fromFile($filePath));
                }
            }
            return $files;
        });
    }

    public static function boot()
    {
        parent::boot();
    }

    public function getPost(string $permalink): ?Post
    {
        $permalink = preg_replace('#^/#', '', $permalink); // Don't want a leading slash for the comparison
        foreach ($this->posts as $post) {
            if (preg_replace('#^/#', '', $post->permalink) == $permalink) {
                return $post;
            }
        }
        return null;
    }
}

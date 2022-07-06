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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BuildCache extends Command
{
    public $signature = 'mdblog:cache';

    public $description = 'Builds the cache for the Markdown Blog';

    public function handle(): int
    {
        // Clean out the database table to make room for the new stuff
        Post::truncate();
        Cache::store(MarkdownBlog::cacheStore())->forget('mdblog.categories');
        Cache::store(MarkdownBlog::cacheStore())->forget('mdblog.tags');

        $publicFolder = config('mdblog.public.path', false);
        throw_if(empty($publicFolder), new \Exception('Public Path not found for MDBlog'));

        $publicPath = public_path($publicFolder);

        throw_if(Str::contains('..', $publicPath), new \Exception('Invalid public assets folder for MDBlog'));

        if (!File::isDirectory($publicPath)) {
            throw_if(!Storage::makeDirectory($publicPath), new \Exception('Could not create folder for public MDBlog assets'));
        }

        // Clean out the public folder if requested
        if (config('mdblog.public.delete')) {
            (new \Illuminate\Filesystem\Filesystem())->cleanDirectory($publicPath);
        }

        // Copy asset files
        $allowedExtensions = preg_split('/\s*?,\s*?/', config('mdblog.public.copy_extensions', ''));
        $allowedExtensions = array_map(function ($v) {
            return Str::start(strtolower($v), '.');
        }, $allowedExtensions);

        $allFiles = File::allFiles(storage_path('mdblog'));
        if (!empty($allFiles)) {
            foreach ($allFiles as $f) {
                if (Str::endsWith(Str::lower($f->getFilename()), ['.md', '.markdown', '.html', '.htm'])) {
                    $post = Post::fromFile($f);
                    $post->save();
                }
                if (Str::endsWith(Str::lower($f->getFilename()), $allowedExtensions)) {
                    // Copy assets to public folder
                    $destDir = dirname($publicPath . '/' . $f->getRelativePathname());
                    if (!File::isDirectory($destDir)) {
                        File::makeDirectory($destDir, 0755, true);
                    }
                    copy($f->getPathname(), $publicPath . '/' . $f->getRelativePathname());
                }
            }
        }

        // Need unique date/time for each post, at least separate seconds so sorting works okay
        if (config('mdblog.fix_date', true)) {
            // This is potentially a long process that might not be needed for everybody, so there
            // should be a way to opt out. 
            $sortOrder = 1;
            $ps = Post::posts()->orderBy('publish_date')->orderBy('filepath')->get();
            foreach ($ps as $p) {
                $p->sort_order = $sortOrder++;
                $p->save();
            }
        }


        // Don't care about the data, but this triggers a cache rebuild
        \Aelora\MarkdownBlog\Models\Category::first();
        \Aelora\MarkdownBlog\Models\Tag::first();

        event(new \Aelora\MarkdownBlog\Events\CacheUpdated());

        return self::SUCCESS;
    }
}

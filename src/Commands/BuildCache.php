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
                    $post = Post::fromFile($f->getPathname());
                    $relativePath = $f->getRelativePath();
                    $post->content = preg_replace_callback('/!\[.*?\]\((.*?)\)/', function ($match) use ($relativePath) {
                        $toReplace = $match[0];
                        $imagePath = $match[1];
                        if (preg_match('/^https?:\/\//', $imagePath) || Str::startsWith($imagePath, '//') || Str::startsWith($imagePath, 'data:')) {
                            // Absolute URL, don't do anything
                            return $toReplace;
                        }

                        $url = url(config('mdblog.public.path') . '/' . $relativePath . '/' . $imagePath);
                        return Str::replace($match[1], $url, $match[0]);
                    }, $post->content);

                    $featuredImage = $post->image;
                    // dump($post->permalink, $post->front_matter['image']);
                    if (empty($featuredImage) || preg_match('/^https?:\/\//', $featuredImage) || Str::startsWith($featuredImage, '//') || Str::startsWith($featuredImage, 'data:')) {
                        // Don't have to do anything, but easier to catch this an do 
                        // nothing that worry about it on the next step
                    } else {
                        $post->image = url(config('mdblog.public.path') . '/' . $relativePath . '/' . $featuredImage);
                    }
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

        $this->error('done');
        return 1;
        die('123');


        $newMatter = [];

        // Load all posts and look for images
        $allPosts = Post::all();
        foreach ($allPosts as $post) {
            // Fix any images so they're pointing to the right place
            $post->content = preg_replace_callback('/!\[.*?\]\((.*?)\)/', function ($match) {
                $toReplace = $match[0];
                $imagePath = $match[1];
                if (preg_match('/^https?:\/\//', $imagePath) || Str::startsWith($imagePath, '//') || Str::startsWith($imagePath, 'data:')) {
                    // Absolute URL, don't do anything
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

            // Look for local image path
            $featuredImage = $post->image;
            if (empty($featuredImage) || preg_match('/^https?:\/\//', $featuredImage) || Str::startsWith($featuredImage, '//') || Str::startsWith($featuredImage, 'data:')) {
                // Don't have to do anything, but easier to catch this an do 
                // nothing that worry about it on the next step
            } else if (file_exists(storage_path('mdblog/' . $featuredImage))) {
                // Can only do this if it exists
                $destPath = public_path(config('mdblog.public_path') . '/' . $featuredImage);
                // Will create the directory if it doesn't exist
                File::makeDirectory(dirname($destPath), 0755, true, true);
                File::copy(storage_path('mdblog/' . $featuredImage), $destPath);
                $post->image = url(config('mdblog.public_path') . '/' . $featuredImage);
            }

            $post->content = $content;
            $post->save();

            // $post->updateContent($content, $newMatter);
        }

        // Don't care about the data, but this triggers a cache rebuild
        \Aelora\MarkdownBlog\Models\Category::first();
        \Aelora\MarkdownBlog\Models\Tag::first();

        return self::SUCCESS;
    }
}

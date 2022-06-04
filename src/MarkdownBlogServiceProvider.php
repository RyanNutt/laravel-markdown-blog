<?php

namespace Aelora\MarkdownBlog;

use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MarkdownBlogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-markdown-blog')
            ->hasConfigFile('mdblog')
            ->hasCommand(Commands\BuildCache::class)
            ->hasCommand(Commands\DownloadRepository::class)
            ->hasViews();
    }

    public function boot()
    {
        parent::boot();
        Str::macro('cleanPath', function (string $path, string $separator = '/') {
            $sections = preg_split('#[\\/]#', $path);
            $sections = array_map(function ($section) {
                $section = str_replace(['&', '@'], ['and', 'at'], $section);
                $section = preg_replace('/\s+?/', ' ', $section);
                return Str::slug(trim($section));
            }, $sections);
            return implode($separator, $sections);
        });
    }
}

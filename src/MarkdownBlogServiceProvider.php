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
            ->hasRoutes(['web'])
            ->hasCommand(Commands\BuildCache::class)
            ->hasCommand(Commands\DownloadRepository::class)
            ->hasViews()
            ->hasMigrations([
                'create_mdblog_table'
            ]);
    }

    public function boot()
    {
        parent::boot();
    }
}

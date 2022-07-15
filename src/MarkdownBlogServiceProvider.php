<?php

namespace Aelora\MarkdownBlog;

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Illuminate\Support\Facades\Blade;
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

        Blade::directive('mdpermalink', function (string $search, string $type = 'post', bool $exact = false, int $field = MarkdownBlog::SEARCH_FILENAME) {
            $p = MarkdownBlog::findPost($search, $type, $exact, $field);
            if (empty($p)) {
                return '#';  // So it doesn't break the page
            }
            return url($p->permalink);
        });
    }
}

<?php

namespace Aelora\MarkdownBlog\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Aelora\MarkdownBlog\MarkdownBlogServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Aelora\\MarkdownBlog\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            MarkdownBlogServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // It has to be there for the service provider to load?
        // if (!is_dir(storage_path('mdblog'))) {
        //     mkdir(storage_path('mdblog'));
        // }

        config()->set('database.default', 'testing');


        $migration = include __DIR__ . '/../database/migrations/create_mdblog_table.php.stub';
        $migration->up();
    }
}

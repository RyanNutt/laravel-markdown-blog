<?php

use Aelora\MarkdownBlog\Models\Post;

/**
 * Test that we're able to download files from a public GitHub repository.
 */

test('Download zip file', function () {
    config()->set('mdblog.repository.url', 'https://github.com/RyanNutt/laravel-markdown-testrepo');
    Artisan::call('mdblog:download');

    $expectedFiles = [
        '600x400.gif',
        '600x400.jpg',
        '600x400.png',
        'file1.md',
        'file2.markdown',
        'file3.html',
        'file4.htm',
        'subfolder/990000.gif',
        'subfolder/990000.jpg',
        'subfolder/990000.png',
        'subfolder/file5.md',
        'subfolder/file6.markdown',
        'subfolder/file7.html',
        'subfolder/file8.htm',
        'subfolder/featuredimage.md',
    ];

    foreach ($expectedFiles as $expFile) {
        expect(file_exists(storage_path('mdblog/' . $expFile)))->toBeTrue();
    }
});

test('Files added to database', function () {
    config()->set('mdblog.repository.url', 'https://github.com/RyanNutt/laravel-markdown-testrepo');
    Artisan::call('mdblog:download');
    expect(count(Post::all()))->toBe(10);
});

test('Expected permalinks', function () {
    config()->set('mdblog.repository.url', 'https://github.com/RyanNutt/laravel-markdown-testrepo');
    Artisan::call('mdblog:download');

    $expectedPermalinks = [
        '/image-tests',
        '/file1',
        '/file2',
        '/file3',
        '/file4',
        '/subfolder/file5',
        '/subfolder/file6',
        '/subfolder/file7',
        '/subfolder/file8',
    ];

    foreach ($expectedPermalinks as $pm) {
        $p = Post::permalink($pm)->first();
        expect($p)->not->toBeNull();
    }
});

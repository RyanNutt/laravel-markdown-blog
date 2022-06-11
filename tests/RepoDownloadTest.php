<?php

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
    ];

    foreach ($expectedFiles as $expFile) {
        expect(file_exists(storage_path('mdblog/' . $expFile)))->toBeTrue();
    }
});

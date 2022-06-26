<?php

/**
 * Test MarkdownBlog::normalizePath function
 */

use Aelora\MarkdownBlog\Facades\MarkdownBlog;

test('Multiple Paths', function () {
    /**
     * Key is path to test, value is the expected return value
     */
    $tests = [
        '/a/normal/path' => '/a/normal/path',
        'a/normal/path' => 'a/normal/path',
        'a/normal/path/' => 'a/normal/path/',
        '/a/normal/path/' => '/a/normal/path/',
        '/with/multiple///slashes' => '/with/multiple/slashes',
        'path/with/./dot/directory' => 'path/with/dot/directory',
        'path/./with/./multiple/./dots' => 'path/with/multiple/dots',
        'dots/at/end/..' => 'dots/at',
        'dots/at/end/2/../' => 'dots/at/end/',
        'multiple/double/dots/../../' => 'multiple/',
        'at/../../beginning' => 'beginning',    // Can't go past root
        '/at/../../beginning/' => '/beginning/',
        './hello/' => 'hello/',
        '././././hello' => 'hello',
    ];
    foreach ($tests as $k => $v) {
        expect(MarkdownBlog::normalizePath($k))->toBe($v);
    }
});

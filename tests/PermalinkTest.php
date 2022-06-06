<?php

/**
 * Tests for permalinks on posts
 */

use Illuminate\Support\Facades\Config;
use Aelora\MarkdownBlog\Models\Post;

// File with defined permalink without leading slash actually has
// the leading slash when loaded. Also tests trailing slash option. 
test('Leading Slash/Trailing', function () {
    Config::set('mdblog.permalinks.trailing_slash', false);
    $p = Post::fromFile(__DIR__ . '/fixtures/permalinks/definedwithoutslash.md');
    expect($p->permalink)->toBe('/this-is-the-permalink');

    Config::set('mdblog.permalinks.trailing_slash', true);
    $p = Post::fromFile(__DIR__ . '/fixtures/permalinks/definedwithoutslash.md');
    expect($p->permalink)->toBe('/this-is-the-permalink/');
});


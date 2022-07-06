<?php

/**
 * Tests for permalinks on posts
 */

use Illuminate\Support\Facades\Config;
use Aelora\MarkdownBlog\Models\Post;
use Symfony\Component\Finder\SplFileInfo;

test('defined without leading slash', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/permalinks/definedwithoutslash.md');
    expect($p->permalink)->toBe('/this-is-the-permalink');
});

test('defined with a trailing slash', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/permalinks/definedwithtrailingslash.md');
    expect($p->permalink)->toBe('/this-is-the-permalink');
});

test('defined with extension', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/permalinks/definedwithextension.md');
    expect($p->permalink)->toBe('/this-is-the-permalink.md');
});

test('no permalink, repo root', function () {
    $f = new SplFileInfo(__DIR__ . '/fixtures/permalinks/nopermalink.md', '/', '/nopermalink.md');
    $p = Post::fromFile($f);
    expect($p->permalink)->toBe('/nopermalink');
});

test('no permalink, subfolder', function () {
    $f = new SplFileInfo(__DIR__ . '/fixtures/permalinks/nopermalink.md', '/2020/06/01/', '/2020/06/01/nopermalink.md');
    $p = Post::fromFile($f);
    expect($p->permalink)->toBe('/2020/06/01/nopermalink');
});

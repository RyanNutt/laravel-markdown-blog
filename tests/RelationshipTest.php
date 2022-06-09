<?php

/**
 * Tests for relationships between posts
 */

use Illuminate\Support\Facades\Config;
use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Support\Facades\Cache;
use Aelora\MarkdownBlog\Facades\MarkdownBlog;


test('Parent->parent_id == 0', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/parenttest');
    $parentPost = Post::permalink('parent-1')->first();
    expect($parentPost->parent_id)->toBe(0);
});

test('Parent has 3 children', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/parenttest');
    $parentPost = Post::permalink('parent-1')->first();
    expect($parentPost->children()->count())->toBe(4);
});

test('Parent child permalinks', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/parenttest');
    $parentPost = Post::permalink('parent-1')->first();
    $childPermalinks = $parentPost->children()->get()->pluck('permalink');
    expect($childPermalinks->toArray())->toEqualCanonicalizing(['/child-3', '/child-2', '/child-1', '/parent-1/child-4']);
});

test('/child-1 parent', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/parenttest');
    $childPost = Post::permalink('child-1')->first();
    $parentPost = $childPost->parent()->first();
    $actualParentPost = Post::permalink('parent-1')->first();
    expect($parentPost->id)->toBe($actualParentPost->id);
});

test('/child-2 parent', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/parenttest');
    $childPost = Post::permalink('child-2')->first();
    $parentPost = $childPost->parent()->first();
    $actualParentPost = Post::permalink('parent-1')->first();
    expect($parentPost->id)->toBe($actualParentPost->id);
});

test('/child-3 parent', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/parenttest');
    $childPost = Post::permalink('child-3')->first();
    $parentPost = $childPost->parent()->first();
    $actualParentPost = Post::permalink('parent-1')->first();
    expect($parentPost->id)->toBe($actualParentPost->id);
});

<?php

// Tests for categories and tags

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Aelora\MarkdownBlog\Models\Category;
use Aelora\MarkdownBlog\Models\Tag;
use Aelora\MarkdownBlog\Models\Post;

test('cat search', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/frontmatter');
    expect(Post::category('category-1')->count())->toBe(1);
    expect(Post::category('Category 1')->count())->toBe(1);
});

test('invalid category', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/frontmatter');
    expect(Post::category('invalid-cat')->count())->toBe(0);
    expect(Post::category('')->count())->toBe(0);
});

test('tag search', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/frontmatter');
    expect(Post::tag('tag-1')->count())->toBe(1);
    expect(Post::tag('Tag 1')->count())->toBe(1);
});

test('invalid tag', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/frontmatter');
    expect(Post::tag('does not exist')->count())->toBe(0);
    expect(Post::tag('')->count())->toBe(0);
});

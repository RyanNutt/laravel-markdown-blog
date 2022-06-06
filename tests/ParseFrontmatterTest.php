<?php

/**
 * Tests that parsing markdown files, specifically the front matter, gets the 
 * expected values. 
 */

use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Support\Facades\File;

beforeEach(function () {
});

// Title parsed out correctly
test('title', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/basicfile.md');
    expect($p->title)->toBe('Demo Title');
});

// Array of categories
test('categories', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/basicfile.md');
    expect(json_decode($p->categories))->toBe(['category-1', 'category-2']);
});

// Array of tags
test('tags', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/basicfile.md');
    expect(json_decode($p->tags))->toBe(['tag-1', 'tag-2']);
});

// Date without time - should be 00:00:00
test('Date without time', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/basicfile.md');
    $this->assertEquals(\Carbon\Carbon::parse('2020-01-01 00:00:00'), $p->date);
});

// Category and tag as sing string instead of array
test('Category and Tag as String', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/stringcategory.md');
    expect(json_decode($p->categories))->toBe(['category-number-1']);
    expect(json_decode($p->tags))->toBe(['tag-number-1']);
});

// No category or tag field
test('No category or tag', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/nocategorytag.md');
    expect(json_decode($p->categories))->toBe([]);
    expect(json_decode($p->tags))->toBe([]);
});

// Category and tag field, but both are blank 
test('Blank category and tag', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/blankcategorytag.md');
    expect(json_decode($p->categories))->toBe([]);
    expect(json_decode($p->tags))->toBe([]);
});

// Singular tag and category properties
test('Singular category and tag', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/singularcategorytag.md');
    expect(json_decode($p->categories))->toBe(['category-number-3', 'category-number-4']);
    expect(json_decode($p->tags))->toBe(['tag-number-7', 'tag-number-8']);
});

// Future dated post, published should be false
test('Future dated post', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/draftfuturedate.md');
    expect($p->published)->toBe(false);
});

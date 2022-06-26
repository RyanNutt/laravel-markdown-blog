<?php

/**
 * Tests that parsing markdown files, specifically the front matter, gets the 
 * expected values. 
 */

use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Support\Arr;
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
    expect($p->categories)->toEqualCanonicalizing(['category-1', 'category-2']);
});

// Array of tags
test('tags', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/basicfile.md');
    expect($p->tags)->toEqualCanonicalizing(['tag-1', 'tag-2']);
});

// Date without time - should be 00:00:00
test('Date without time', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/basicfile.md');
    $this->assertEquals(\Carbon\Carbon::parse('2020-01-01 00:00:00'), $p->publish_date);
});

// Category and tag as sing string instead of array
test('Category and Tag as String', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/stringcategory.md');
    expect($p->categories)->toEqualCanonicalizing(['category-number-1']);
    expect($p->tags)->toEqualCanonicalizing(['tag-number-1']);
});

// No category or tag field
test('No category or tag', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/nocategorytag.md');
    expect($p->categories)->toBe([]);
    expect($p->tags)->toBe([]);
});

// Category and tag field, but both are blank 
test('Blank category and tag', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/blankcategorytag.md');
    expect($p->categories)->toBe([]);
    expect($p->tags)->toBe([]);
});

// Singular tag and category properties
test('Singular category and tag', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/singularcategorytag.md');
    expect($p->categories)->toEqualCanonicalizing(['category-number-3', 'category-number-4']);
    expect($p->tags)->toEqualCanonicalizing(['tag-number-7', 'tag-number-8']);
});

// Future dated post, published should be false
test('Future dated post', function () {
    $p = Post::fromFile(__DIR__ . '/fixtures/frontmatter/draftfuturedate.md');
    expect($p->published)->toBe(false);
});

// hasFrontMatter tests
test('hasFrontMatter(null)', function () {
    $p = new Post();
    $testValues = [null, [], ''];
    foreach ($testValues as $tv) {
        $p->front_matter = $tv;
        expect($p->hasFrontMatter())->toBeFalse();
    }
});

test('hasFrontMatter(null) true', function () {
    $p = new Post();
    $testValues = [['yes'], 'hi'];
    foreach ($testValues as $tv) {
        $p->front_matter = $tv;
        expect($p->hasFrontMatter())->toBeTrue();
    }
});

test('hasFrontMatter($value) - true', function () {
    $testValues = [];
    Arr::set($testValues, 'somekey', '1');
    Arr::set($testValues, 'anotherkey', '2');
    Arr::set($testValues, 'nested.value', 1);

    $p = new Post();
    $p->front_matter = $testValues;

    expect($p->hasFrontMatter('somekey'))->toBeTrue();
    expect($p->hasFrontMatter('anotherkey'))->toBeTrue();
    expect($p->hasFrontMatter('nested.value'))->toBeTrue();

    expect($p->hasFrontMatter('whatever'))->toBeFalse();
    expect($p->hasFrontMatter(''))->toBeFalse();
});

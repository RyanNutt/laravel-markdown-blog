<?php

/**
 * Tests for working with file paths related to the blog posts or requested images
 */

use Aelora\MarkdownBlog\Models\Post;

beforeEach(function () {
    config([
        'app.url' => 'http://example.com',
        'mdblog.public.path' => 'assets/blog',
    ]);
});

/**
 * Test that a relative path is correctly pulled from a full file path.
 * This does have to force the ->filepath and ->fullpath attributes of the
 * post to be preceeded by storage_path('mdblog') since it's pulling files
 * from fixtures that are outside of that path. We don't care if those files
 * actually exist in the storage_path, but we care that the relative path
 * is correct if they were.
 */
it('relative path - repo root', function () {
    $p = new Post();
    $p->fullpath = storage_path('mdblog/testfile.md');
    expect($p->relativePath())->toBe('/');
});

it('relative path - repo root, image', function () {
    $p = new Post();
    $p->fullpath = storage_path('mdblog/testfile.md');
    expect($p->relativePath('image.png'))->toBe('/image.png');
});

it('relative path - subfolder', function () {
    $p = new Post();
    $p->fullpath = storage_path('mdblog/subfolder/another/testfile.md');
    expect($p->relativePath())->toBe('/subfolder/another/');
});

it('relative path - subfolder, image', function () {
    $p = new Post();
    $p->fullpath = storage_path('mdblog/subfolder/another/testfile.md');
    expect($p->relativePath('image.png'))->toBe('/subfolder/another/image.png');
});

it('url test - root', function () {
    config([
        'app.url' => 'http://example.com',
        'mdblog.public.path' => 'assets/blog'
    ]);
    $p = new Post();
    $p->fullpath = storage_path('mdblog/testfile.md');
    expect($p->publicPath('image.png'))->toBe('/assets/blog/image.png');
});

it('url test - subfolder', function () {
    $p = new Post();
    $p->fullpath = storage_path('mdblog/subfolder/anotherone/third/test.md');
    expect($p->publicPath('image.png'))->toBe('/assets/blog/subfolder/anotherone/third/image.png');
});


test('image path - markdown relative to root', function () {
    $mdtext = "\nHello\n   ![](image.png)   \n\nthe end";
    $expectedText = "\nHello\n   ![](/assets/blog/image.png)   \n\nthe end";
    $p = new Post();
    $p->fullpath = storage_path('mdblog/test.md');
    $p->content = $mdtext;

    expect($p->content)->toContain('src="/assets/blog/image.png"');
});

test('image path - markdown relative to repo root', function () {
    $mdText = "\nHello\n   ![](/image.png)\n  \nthe end";
    $expectedText = "\nHello\n   ![](/assets/blog/image.png)\n  \nthe end";
    $p = new Post();
    $p->fullpath = storage_path('mdblog/subfolder/test.md');
    $p->content = $mdText;
    expect($p->content)->toContain('src="/assets/blog/image.png"');
});

test('markdown with alt text', function () {
    $mdText = "   ![some text](/image.png)   ";
    $expectedText = "   ![some text](/assets/blog/image.png)   ";
    $p = new Post();
    $p->fullpath = storage_path('mdblog/subfolder.test.md');
    $p->content = $mdText;
    expect($p->content)->toContain('src="/assets/blog/image.png"');
});

test('image in subfolder of post', function () {
    $mdText = '![](sub/image.png)';
    $expText = '![](/assets/blog/subfolder/sub/image.png)';
    $p = new Post();
    $p->fullpath = storage_path('mdblog/subfolder/test.md');
    $p->content = $mdText;
    expect($p->content)->toContain('src="/assets/blog/subfolder/sub/image.png"');
});

test('image in parent folder from post', function () {
    $mdText = '![](../image.png)';
    $expText = '![](/assets/blog/image.png)';
    $p = new Post();
    $p->fullpath = storage_path('mdblog/subfolder/test.md');
    $p->content = $mdText;
    expect($p->content)->toContain('src="/assets/blog/image.png"');
});

test('image in folder with leading dot', function () {
    $mdText = '![](./image.png)';
    $expText = '![](/assets/blog/subfolder/image.png)';
    $p = new Post();
    $p->fullpath = storage_path('mdblog/subfolder/test.md');
    $p->content = $mdText;
    expect($p->content)->toContain('src="/assets/blog/subfolder/image.png"');
});

test('html only attribute', function () {
    $html = '<img src="image.png">';
    $exp = '<img src="/assets/blog/image.png">';
    $p = new Post();
    $p->fullpath = storage_path('mdblog/test.md');
    $p->content = $html;
    expect($p->content)->toBe($exp);
});

test('html first attribute', function () {
    $html = '<img src="image.png" data-attribute="some data">';
    $exp = '<img src="/assets/blog/image.png" data-attribute="some data">';
    $p = new Post();
    $p->fullpath = storage_path('mdblog/test.md');
    $p->content = $html;
    expect($p->content)->toBe($exp);
});

test('html last attribute', function () {
    $html = '<img id="whatever" src="image.png">';
    $exp = '<img id="whatever" src="/assets/blog/image.png">';
    $p = new Post();
    $p->fullpath = storage_path('mdblog/test.md');
    $p->content = $html;
    expect($p->content)->toBe($exp);
});

test('html middle attribute', function () {
    $html = '<img id="testing" src="image.png" class="some class name">';
    $exp = '<img id="testing" src="/assets/blog/image.png" class="some class name">';
    $p = new Post();
    $p->fullpath = storage_path('mdblog/test.md');
    $p->content = $html;
    expect($p->content)->toBe($exp);
});

// Image front matter property tests
test('Front Matter: full url', function () {
    $p = new Post();
    $p->image = 'https://example.com/test.png';
    expect($p->image)->toBe('https://example.com/test.png');
});

test('Front Matter: repo root', function () {
    $p = new Post();
    $p->image = '/a/test/image.png';
    expect($p->image)->toBe('/assets/blog/a/test/image.png');
});

test('Front Matter: post folder', function () {
    $p = new Post();
    $p->image = 'test.png';
    $p->fullpath = storage_path('mdblog/a/folder/test.md');
    expect($p->image)->toBe('/assets/blog/a/folder/test.png');
});

test('Front Matter: post folder with dot', function () {
    $p = new Post();
    $p->image = './test.png';
    $p->fullpath = storage_path('mdblog/a/folder/test.md');
    expect($p->image)->toBe('/assets/blog/a/folder/test.png');
});

test('Front Matter: post subfolder', function () {
    $p = new Post();
    $p->image = 'subfolder/test.png';
    $p->fullpath = storage_path('mdblog/a/folder/test.md');
    expect($p->image)->toBe('/assets/blog/a/folder/subfolder/test.png');
});

test('Front Matter: post parent folder', function () {
    $p = new Post();
    $p->image = '../test.png';
    $p->fullpath = storage_path('mdblog/a/folder/test.md');
    expect($p->image)->toBe('/assets/blog/a/test.png');
});

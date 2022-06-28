<?php

// Test file to play around with, but not really testing anything

use Aelora\MarkdownBlog\Models\Category;
use Aelora\MarkdownBlog\Models\Tag;
use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Support\Facades\DB;

test('x', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures');
    $p = Post::category('category-1')->get();
    dump($p);
});

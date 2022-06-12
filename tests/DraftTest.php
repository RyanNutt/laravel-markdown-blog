<?php

/**
 * Testing draft states of posts
 */

use Aelora\MarkdownBlog\Facades\MarkdownBlog;
use Aelora\MarkdownBlog\Models\Post;

// Should have 3 posts since we're not scoping out drafts
test('all posts without draft scope', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/drafttests');
    $this->assertCount(3, Post::all());
});

test('only current post in table', function () {
    MarkdownBlog::buildCache(__DIR__ . '/fixtures/drafttests');
    $this->assertCount(1, Post::published()->get());

    $post = Post::published()->permalink('/current-post')->first();
    expect($post->content)->toContain('not a draft');
});

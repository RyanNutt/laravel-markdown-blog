<?php

namespace Aelora\MarkdownBlog\Http\Controllers;

use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Routing\Controller;

/**
 * Controller for an individual post
 */
class PostController extends Controller
{

    public function index()
    {
        $post = Post::current();
        abort_if(empty($post), 404);

        if ($post->hasFrontMatter('noview')) {
            header('Content-type: ' . $post->getFrontMatter('content-type', 'text/plain'));
            die($post->content);
        }

        return view('markdown-blog::post', [
            'post' => $post,
        ]);
    }
}

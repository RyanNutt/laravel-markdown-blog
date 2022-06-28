<?php

namespace Aelora\MarkdownBlog\Http\Controllers;

use Illuminate\Routing\Controller;
use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Http\Request;

class Sitemap extends Controller
{

    public function index(Request $request)
    {
        $posts = Post::published()->get();
        return response()->view('markdown-blog::sitemap', [
            'posts' => $posts,
        ])->header('Content-Type', 'text/xml');
    }
}

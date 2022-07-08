<?php

namespace Aelora\MarkdownBlog\Http\Controllers;

use Illuminate\Routing\Controller;
use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Http\Request;

class Sitemap extends Controller
{

    public function index(Request $request)
    {
        if (!config('mdblog.sitemap.enabled', true)) {
            // Route shouldn't be there, so this shouldn't ever happen, 
            // but just in case...
            return response('Sitemap is disabled', 404);
        }

        $posts = Post::published()
            ->whereNotIn('type', config('mdblog.sitemap.exclude', []))
            ->get();
        return response()->view('markdown-blog::sitemap', [
            'posts' => $posts,
        ])->header('Content-Type', 'text/xml');
    }
}

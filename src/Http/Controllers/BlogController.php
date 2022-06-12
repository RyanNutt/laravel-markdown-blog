<?php

namespace Aelora\MarkdownBlog\Http\Controllers;

use Illuminate\Routing\Controller;
use Aelora\MarkdownBlog\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{

    public function index(Request $request)
    {
        // Anything <= 1 runs as page 1, I don't want that
        abort_if($request->get('page', 1) < 1, 404);

        $paginator = Post::posts()->published()->orderBy('date', 'DESC')->paginate(config('mdblog.per_page'));
        abort_if($paginator->count() <= 0, 404);
        return view('markdown-blog::blog', [
            'posts' => $paginator,
        ]);
    }
}

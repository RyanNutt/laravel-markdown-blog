<?php

namespace Aelora\MarkdownBlog\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Aelora\MarkdownBlog\Models\Tag;
use Aelora\MarkdownBlog\Models\Post;

class TagController extends Controller
{
    public function index(Request $request, string $slug)
    {
        // Anything <= 1 runs as page 1, I don't want that
        abort_if($request->get('page', 1) < 1, 404);

        $tag = Tag::where('slug', $slug)->first();
        abort_if(empty($tag), 404);

        $paginator = Post::posts()->tag($slug)->orderBy('publish_date', 'DESC')->paginate(config('mdblog.per_page'));
        abort_if($paginator->count() <= 0, 404);

        return view($tag->meta('view', 'markdown-blog::tag'), [
            'posts' => $paginator,
            'tag' => $tag,
        ]);
    }
}

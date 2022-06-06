<?php

namespace Aelora\MarkdownBlog\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Aelora\MarkdownBlog\Models\Category;
use Aelora\MarkdownBlog\Models\Post;

class CategoryController extends Controller
{
    public function index(Request $request, string $slug)
    {
        // Anything <= 1 runs as page 1, I don't want that
        abort_if($request->get('page', 1) < 1, 404);

        $cat = Category::where('slug', $slug)->first();
        abort_if(empty($cat), 404);

        $paginator = Post::posts()->category($slug)->orderBy('date', 'DESC')->paginate(config('mdblog.per_page'));
        abort_if($paginator->count() <= 0, 404);

        return view('markdown-blog::category', [
            'posts' => $paginator,
            'category' => $cat,
        ]);
    }
}

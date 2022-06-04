<?php

namespace Aelora\MarkdownBlog\Http\Controllers;

use Illuminate\Routing\Controller;

/**
 * Controller for an individual post
 */
class PostController extends Controller
{

    public function index()
    {
        return view('markdown-blog::index');
    }
}

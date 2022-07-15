<?php

namespace Aelora\MarkdownBlog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Aelora\MarkdownBlog\MarkdownBlog
 */
class MarkdownBlog extends Facade
{

    // @see https://stackoverflow.com/q/23345347/1561431 
    const SEARCH_PERMALINK = \Aelora\MarkdownBlog\MarkdownBlog::SEARCH_PERMALINK;
    const SEARCH_TITLE = \Aelora\MarkdownBlog\MarkdownBlog::SEARCH_TITLE;
    const SEARCH_FILENAME = \Aelora\MarkdownBlog\MarkdownBlog::SEARCH_FILENAME;

    protected static function getFacadeAccessor()
    {
        return \Aelora\MarkdownBlog\MarkdownBlog::class;
    }
}

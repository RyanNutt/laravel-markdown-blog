<?php

namespace Aelora\MarkdownBlog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Aelora\MarkdownBlog\MarkdownBlog
 */
class MarkdownBlog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Aelora\MarkdownBlog\MarkdownBlog::class;
    }
}

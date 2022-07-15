<?php

namespace Aelora\MarkdownBlog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Aelora\MarkdownBlog\MarkdownBlog
 */
class Markdown extends Facade
{

    protected static function getFacadeAccessor()
    {
        return \Aelora\MarkdownBlog\Markdown::class;
    }
}

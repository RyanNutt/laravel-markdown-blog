<?php

namespace Aelora\MarkdownBlog;

use Str;

class MarkdownBlog
{

    /**
     * Returns the name of the cache store to use for the scanned posts
     */
    public function cacheStore(): string
    {
        $out = config('mdblog.cache');
        if (empty($out) || $out == 'default') {
            return config('cache.default');
        }
        return $out;
    }



    /**
     * Returns the controller for a single post. Used when there is already
     * a catch all route in the app and it needs to be able to hand off
     * to the post controller. 
     */
    public function postController()
    {
        $controllerInfo = explode('@', config('mdblog.controllers.post'));
        return call_user_func([new $controllerInfo[0], $controllerInfo[1]]);
    }

    public function cleanPath(string $path, string $separator = '/')
    {
        $sections = preg_split('#[\\/]#', $path);
        $sections = array_map(function ($section) {
            $section = str_replace(['&', '@'], ['and', 'at'], $section);
            $section = preg_replace('/\s+?/', ' ', $section);
            return Str::slug(trim($section));
        }, $sections);
        return implode($separator, $sections);
    }
}

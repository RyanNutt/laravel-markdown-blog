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

    /** @see https://gist.github.com/AlexR1712/83e502e25300fcfeb4199b88f3bdce49 */
    public function bladeCompile($value, array $args = [])
    {
        $args = array_merge($args, [
            '__env' => app(\Illuminate\View\Factory::class),
        ]);
        $generated = \Blade::compileString($value);

        ob_start() and extract($args, EXTR_SKIP);

        // We'll include the view contents for parsing within a catcher
        // so we can avoid any WSOD errors. If an exception occurs we
        // will throw it out to the exception handler.
        try {
            eval('?>' . html_entity_decode($generated));
        }

        // If we caught an exception, we'll silently flush the output
        // buffer so that no partially rendered views get thrown out
        // to the client and confuse the user with junk.
        catch (\Exception $e) {
            ob_get_clean();
            throw $e;
        }

        $content = ob_get_clean();

        return $content;
    }
}

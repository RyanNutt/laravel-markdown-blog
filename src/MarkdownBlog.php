<?php

namespace Aelora\MarkdownBlog;

use Illuminate\Support\Facades\File;
use Str;
use Aelora\MarkdownBlog\Models\Post;

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

    /**
     * Fill the mdblog table with post information from the markdown / html
     * files that were downloaded.
     * 
     * This could probably be way more efficient, but since it only happens on
     * update I'm not too worried about it right now. 
     */
    public function buildCache($fileDir = false)
    {
        // Empty the cache table
        \Aelora\MarkdownBlog\Models\Post::truncate();

        if ($fileDir === false) {
            $fileDir = storage_path('mdblog');
        }

        throw_if(!file_exists($fileDir), new \Exception($fileDir . ' does not exist'));

        $allFiles = File::allFiles($fileDir);
        if (!empty($allFiles)) {
            foreach ($allFiles as $file) {
                $lcFilename = Str::lower($file->getFilename());
                if (Str::endsWith($lcFilename, ['.md', '.markdown', '.htm', '.html'])) {
                    // Only going to process markdown or html files
                    $p = Post::fromFile($file->getPathname());
                    $p->save();
                }
            }
        }

        // Have to go back and fill in parent_id. This has to be done after they're all
        // loaded so the full set can be queried. 
        $allPosts = Post::where('parent_id', 0)->notType('post')->get();
        foreach ($allPosts as $currentPost) {
            $fm = $currentPost->front_matter;
            if (!empty($fm['children'])) {
                foreach ($fm['children'] as $child) {
                    $childPost = Post::bestMatch($child, $currentPost->type)->first();
                    if (!empty($childPost)) {
                        $childPost->parent_id = $currentPost->id;
                        $childPost->save();
                    }
                }
            } else {
                // Try and get children from permalink
                $childPosts = Post::where('permalink', 'LIKE', $currentPost->permalink . '%')->get();
                if (!empty($childPosts)) {
                    foreach ($childPosts as $child) {
                        if (Str::beforeLast($child->permalink, '/') == $currentPost->permalink) {
                            $child->parent_id = $currentPost->id;
                            $child->save();
                        }
                    }
                }
            }

            if (!empty($fm['parent'])) {
                // Possible that this overrides a parent_id set by children in the front 
                // matter, but the parent field should have priority so it's okay for
                // it to override. 
                // $parentPost = Post::bestMatch($fm['parent'], $currentPost->type)->first();
                $parentPost = Post::permalink($fm['parent'])->first();
                if (!empty($parentPost)) {
                    $currentPost->parent_id = $parentPost->id;
                    $currentPost->save();
                }
            } else {
                // Try and get parent by the permalink
                $parentPost = Post::permalink(Str::beforeLast($currentPost->permalink, '/'))->first();
                if (!empty($parentPost)) {
                    $currentPost->parent_id = $parentPost->id;
                    $currentPost->save();
                }
            }
        }
    }
}

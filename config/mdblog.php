<?php
// config for Aelora/MarkdownBlog
return [
    'repository' => [
        // Full Url to the repository that holds the blog posts
        'url' => env('MDBLOG_REPO', ''),

        'branch' => env('MDBLOG_BRANCH', 'main'),

        // Either github or gitlab. Ignored if the repository url is *.github.com
        // or *.gitlab.com. Used in case the domain is self hosted and is something
        // different. 
        'type' => env('MDBLOG_TYPE', 'github'),

        // API Key to access repository. Only strictly needed if the repo is not public.
        'key' => env('MDBLOG_KEY', ''),
    ],

    'permalinks' => [
        // All blog pages
        'blog' => env('MDBLOG_PERMALINK_BLOG', '/posts'),

        // Only pages within a specified category
        'categories' => env('MDBLOG_PERMALINK_CATEGORY', '/posts/cat/{slug}'),

        // Only pages with a specified tag
        'tags' => env('MDBLOG_PERMALINK_TAG', '/posts/tag/{slug}'),
    ],

    'controllers' => [
        'blog' => '\Aelora\MarkdownBlog\Http\Controllers\BlogController@index',
        'category' => '\Aelora\MarkdownBlog\Http\Controllers\CategoryController@index',
        'tag' => '\Aelora\MarkdownBlog\Http\Controllers\TagController@index',
        'post' => '\Aelora\MarkdownBlog\Http\Controllers\PostController@index',
        'webhook' => '\Aelora\MarkdownBlog\Http\Controllers\WebhookController@index',
    ],

    'webhook' => [
        'key' => env('MDBLOG_WEBHOOK_KEY', 'change_this_to_something_secure'),
        'route' => env('MDBLOG_WEBHOOK_ROUTE', '/mdblogwebhook'),
    ],

    // Number of posts shown per page on blog
    'per_page' => env('MDBLOG_PER_PAGE', 10),

    /**
     * Settings for the folder where publicly accessible assets will be copied
     * when the repository is downloaded. 
     */
    'public' =>  [
        /**
         * Base path, relative to public folder, where to copy asset files that should
         * be publicly accessible. These will normally be image, CSS or JS files; although
         * you can specify additional extensions below. The path is is relative to the 
         * public folder. 
         */
        'path' => env('MDBLOG_PUBLIC_PATH', 'assets/blog'),

        /**
         * Should the base public folder be cleared out when the cache is rebuilt. Be careful
         * with this. It should be set to false if you're using a folder for mdblog.public.path 
         * that contains files other than ones handled by this package. 
         */
        'delete' => env('MDBLOG_DELETE_PUBLIC_PATH', true),

        /**
         * Extensions in this list will be copied to the public assets folder. Everything else
         * will be ignored. Case insensitive. 
         */
        'copy_extensions' => env('MDBLOG_PUBLIC_EXTENSIONS', 'jpg,jpeg,gif,png,css,js'),
    ],

    /**
     * Options for how posts are rendered
     */
    'render' => [
        // Whether posts are rendered using the blade engine before output
        'blade' => env('MDBLOG_RENDER_BLADE', true),

        // Should markdown be converted to HTML through Parsedown
        'html' => env('MDBLOG_RENDER_HTML', true),

        // Should posts be output in raw Markdown. This overrides both .blade
        // and .html in this config set. 
        'raw' => env('MDBLOG_RENDER_RAW', false),
    ],
];

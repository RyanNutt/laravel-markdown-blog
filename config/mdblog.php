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

        // If true, files named readme.md will not be included in the import. This will 
        // let you use readme.md files for documentation for your blog without it getting
        // imported to your live site. 
        'ignore_readme' => env('MDBLOG_IGNORE_README', true),
    ],

    'permalinks' => [
        // All blog pages
        'blog' => env('MDBLOG_PERMALINK_BLOG', '/posts'),

        // Only pages within a specified category
        'categories' => env('MDBLOG_PERMALINK_CATEGORY', '/posts/cat/{slug}'),

        // Only pages with a specified tag
        'tags' => env('MDBLOG_PERMALINK_TAG', '/posts/tag/{slug}'),

        // Sitemap - Set to false to disable
        'sitemap' => env('MDBLOG_PERMALINK_SITEMAP', '/md-sitemap.xml'),
    ],

    'controllers' => [
        'blog' => '\Aelora\MarkdownBlog\Http\Controllers\BlogController@index',
        'category' => '\Aelora\MarkdownBlog\Http\Controllers\CategoryController@index',
        'tag' => '\Aelora\MarkdownBlog\Http\Controllers\TagController@index',
        'post' => '\Aelora\MarkdownBlog\Http\Controllers\PostController@index',
        'webhook' => '\Aelora\MarkdownBlog\Http\Controllers\WebhookController@index',
        'sitemap' => '\Aelora\MarkdownBlog\Http\Controllers\Sitemap@index',
    ],

    'webhook' => [
        'key' => env('MDBLOG_WEBHOOK_KEY'),
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
     * Options for how posts are rendered. These can also be set in front matter for 
     * individual posts. 
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

    // Options for the xml sitemap
    'sitemap' => [
        'enabled' => env('MDBLOG_SITEMAP', true),

        // List of post types that should be excluded from the sitemap. Should
        // be all lower cased.
        'exclude' => [],

        'priority' => [
            'default' => 0.5,
            // 'product' => 0.75,
            // 'review' => 0.25,
        ],

        'frequency' => [
            'default' => 'weekly',
            // 'product' => 'daily',
            // 'review' => 'monthly',
        ],

    ]
];

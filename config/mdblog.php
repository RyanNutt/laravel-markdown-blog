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

        // If true, all permalinks will end with a trailing slash even if it's 
        // not part of a defined permalink
        'trailing_slash' => env('MDBLOG_PERMALINK_TRAILING_SLASH', true),
    ],

    'controllers' => [
        'blog' => '\Aelora\MarkdownBlog\Http\Controllers\BlogController@index',
        'category' => '\Aelora\MarkdownBlog\Http\Controllers\CategoryController@index',
        'tag' => '\Aelora\MarkdownBlog\Http\Controllers\TagController@index',
        'post' => '\Aelora\MarkdownBlog\Http\Controllers\PostController@index',
    ],

    'webhook' => [
        'key' => env('MDBLOG_WEBHOOK_KEY', 'change_this_to_something_secure'),
        'route' => env('MDBLOG_WEBHOOK_ROUTE', '/mdblogwebhook'),
    ],

    // Number of posts shown per page on blog
    'per_page' => env('MDBLOG_PER_PAGE', 10),

    'cache' => env('MDBLOG_CACHE', 'default'),

    // Path to store images that are downloaded from the git repository so that they
    // can be publicly accessible. This is relative to the public_path of the application.
    'public_path' => env('MDBLOG_PUBLIC_PATH', 'assets/blog'),

    // Delete files from the public path when a new copy of the repo is downloaded. Be 
    // careful with this if public_path is a folder where other files are stored.
    'delete_public_path' => env('MDBLOG_DELETE_PUBLIC_PATH', true),
];

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

    'cache' => env('MDBLOG_CACHE', 'default'),

    // Whether the default route is loaded. It should be unless you're already loading 
    // a default route, in which case that controller should check for a post before
    // throwing a 404. 
    'route' => env('MDBLOG_ROUTE', false),
];

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

        // If true, the zip file downloaded will be compared to the hash of the last download,
        // and if it matches the import will not be run. 
        'check_hash' => env('MDBLOG_CHECK_HASH', true),
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

        // If true, will download the zip file on a successful webhook call. If false, it will
        // clear the cached hash (see repository.check_hash) so that the next time mdblog:download
        // is run the zip will be uncompressed and cache rebuilt. 
        'download' => env('MDBLOG_WEBHOOK_DOWNLOAD', true),
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

    // Options for how markdown is rendered and what settings CommonMark will use.
    // Setting any of the individual extension sets to false will disable that 
    // extension. 
    // 
    // See: https://commonmark.thephpleague.com/2.3/extensions/overview/ for information
    // about individual options. 
    'markdown' => [
        // GitHub Flavored Markdown. This enables several other extensions and is disabled
        // by default
        // See: https://commonmark.thephpleague.com/2.3/extensions/gfm/
        'gfm' => true, // true or false, no config options

        // Attributes extension. No config options, so either true or false.
        // See: https://commonmark.thephpleague.com/2.3/extensions/attributes/
        'attributes' => true,

        // See: https://commonmark.thephpleague.com/2.3/extensions/autolinks/
        'autolink' => true,

        // See: https://commonmark.thephpleague.com/2.3/extensions/description-lists/
        'description_list' => true,

        // See: https://commonmark.thephpleague.com/2.3/extensions/strikethrough/
        'strikethrough' => false,

        // Set to false to disable extension and allow all HTML tags.
        // See: https://commonmark.thephpleague.com/2.3/extensions/disallowed-raw-html/
        'disallowed_raw_html' => [
            'disallowed_tags' => ['title', 'textarea', 'style', 'xml', 'iframe', 'noembed', 'noframes', 'script', 'plaintext'],
        ],

        // See: https://commonmark.thephpleague.com/2.3/extensions/external-links/
        'external_link' => [
            'internal_hosts' => parse_url(env('APP_URL', 'localhost'), PHP_URL_HOST),
            'open_in_new_window' => false,
            'html_class' => 'external-link',
            'nofollow' => '',
            'noopener' => 'external',
            'noreferrer' => 'external',
        ],

        // See: https://commonmark.thephpleague.com/2.3/extensions/footnotes/
        'footnote' => [
            'backref_class'      => 'footnote-backref',
            'backref_symbol'     => '↵',  // carriage return
            'container_add_hr'   => true,
            'container_class'    => 'footnotes',
            'ref_class'          => 'footnote-ref',
            'ref_id_prefix'      => 'fnref:',
            'footnote_class'     => 'footnote',
            'footnote_id_prefix' => 'fn:',
        ],

        // See: https://commonmark.thephpleague.com/2.3/extensions/heading-permalinks/
        // This must be enabled for the TOC extension to work
        'heading_permalink' => [
            'html_class' => 'heading-permalink',
            'id_prefix' => 'content',
            'fragment_prefix' => 'content',
            'insert' => 'before',
            'min_heading_level' => 1,
            'max_heading_level' => 6,
            'title' => 'Permalink',
            'symbol' => '¶', // paragraph symbol
            'aria_hidden' => true,
        ],

        // Mentions extension. Read the documentation on the CommonMark website to see
        // how this one works. It's defaulting to an empty array, which essentially 
        // disables the extension.
        // See: https://commonmark.thephpleague.com/2.3/extensions/mentions/
        'mentions' => false,

        // See: https://commonmark.thephpleague.com/2.3/extensions/smart-punctuation/
        // This is disabled by default because we assume most people aren't going to
        // want this on their website. 
        'smartpunct' => false,

        // See: https://commonmark.thephpleague.com/2.3/extensions/table-of-contents/
        'table_of_contents' => [
            'html_class' => 'mdblog-toc',
            'position' => 'placeholder',
            'style' => 'bullet',
            'min_heading_level' => 1,
            'max_heading_level' => 6,
            'normalize' => 'relative',
            'placeholder' => '[toc]',
        ],

        // See: https://commonmark.thephpleague.com/2.3/extensions/tables/
        'table' => [
            'wrap' => [
                'enabled' => false,
                'tag' => 'div',
                'attributes' => [],
            ],
        ],

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

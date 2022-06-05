
# Laravel Markdown Blog Package

Are you both a Laravel and Jekyll fan? Do you want to write your blog in Markdown?

With this package you'll be able to create your content pages in markdown in a GitHub repository (GitLab is coming) and have that content published in your Laravel site. 


## Installation

You can install the package via composer:

```bash
composer require aelora/laravel-markdown-blog
```


You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-markdown-blog-config"
```

Config settings

|Setting|.env Variable|Description|
|:---|:---|:---|
|cache|MDBLOG_CACHE|Name of the cache driver to use. `default` will use whatever is set as default in `config/cache.php`, or you can use any of the named drivers.|
|controllers.blog||
|controllers.categories||
|controllers.post||
|controllers.tags||
|per_page|MDBLOG_PER_PAGE|Number of blog posts per index page|
|permalinks.blog|MDBLOG_PERMALINK_BLOG|Base permalink for the blog index page|
|permalinks.categories|MDBLOG_PERMALINK_CATEGORY|Base permalink for the category pages|
|permalinks.tags|MDBLOG_PERMALINK_TAG|Base permalink for the tag pages|
|public_path_delete|MDBLOG_DELETE_PUBLIC_PATH|If true, the `public_path` is cleared of all files when a fresh copy of the repository is downloaded.|
|public_path|MDBLOG_PUBLIC_PATH|Path where image files are stored so they can be accessed on public site. Relative to `public_path()`. This should not be a folder that holds anything other that files download from the repository, especially if you're using `public_path_delete`|
|repository.branch|MDBLOG_BRANCH|Which git branch to pull from. This is also the only branch that will trigger updates if you're using webhooks|
|repository.key|MDBLOG_KEY|Personal access token for the repository. Optional for public repositories. Required for private repos.|
|repository.type|MDBLOG_TYPE|Either github or gitlab. The package tries to guess from the url, but in case you're using a custom domain you can set it manually.  Only github is currently supported.|
|repository.url|MDBLOG_REPO|The Url of the repository to pull files from|
|webhook.key|MDBLOG_WEBHOOK_KEY|The key to use for webhooks|
|webhook.route|MDBLOG_WEBHOOK_ROUTE||

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-markdown-blog-views"
```

## Usage

```php
// @TODO
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Credits

- [Ryan Nutt](https://github.com/RyanNutt)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

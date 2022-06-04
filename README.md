
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

This is the contents of the published config file:

```php
return [
];
```

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

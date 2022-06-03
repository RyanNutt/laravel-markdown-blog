
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# Laravel blog package with markdown and git backend

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aelora/laravel-markdown-blog.svg?style=flat-square)](https://packagist.org/packages/aelora/laravel-markdown-blog)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/aelora/laravel-markdown-blog/run-tests?label=tests)](https://github.com/aelora/laravel-markdown-blog/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/aelora/laravel-markdown-blog/Check%20&%20fix%20styling?label=code%20style)](https://github.com/aelora/laravel-markdown-blog/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/aelora/laravel-markdown-blog.svg?style=flat-square)](https://packagist.org/packages/aelora/laravel-markdown-blog)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-markdown-blog.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-markdown-blog)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require aelora/laravel-markdown-blog
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-markdown-blog-migrations"
php artisan migrate
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
$markdownBlog = new Aelora\MarkdownBlog();
echo $markdownBlog->echoPhrase('Hello, Aelora!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ryan Nutt](https://github.com/RyanNutt)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[![Latest Stable Version](http://poser.pugx.org/omisai/laravel-szamlazzhu/v)](https://packagist.org/packages/omisai/laravel-szamlazzhu)
[![Latest Unstable Version](http://poser.pugx.org/omisai/laravel-szamlazzhu/v/unstable)](https://packagist.org/packages/omisai/laravel-szamlazzhu)
[![License](http://poser.pugx.org/omisai/laravel-szamlazzhu/license)](https://packagist.org/packages/omisai/laravel-szamlazzhu)
[![PHP Version Require](http://poser.pugx.org/omisai/laravel-szamlazzhu/require/php)](https://packagist.org/packages/omisai/laravel-szamlazzhu)

## About

**laravel-szamlazzhu** is a Laravel package that provides an easy-to-use interface for communicating with the Számlázz.hu API. It was created by refactoring the original source code (available at [here](https://docs.szamlazz.hu/#php-api/)) and integrating it into the [Laravel framework](https://laravel.com/).

Many of the original source code files were reforged to use the built-in features of Laravel, such as HTTP client, Filesystem abstraction, Configuration and service provider. As a result, **laravel-szamlazzhu** provides a more streamlined and idiomatic way of interacting with the Számlázz.hu API.

## Installation

> **Requires:**
- **[PHP 8.1+](https://php.net/releases/)**
- **[Laravel 9.0+](https://github.com/laravel/laravel)**

To get started with package, simply install it via Composer:

``` bash
composer require omisai/laravel-szamlazzhu
```


## Configuration

Configure your API credentials in .env:

``` env
SZAMLAZZHU_API_KEY=<yourAPIToken>
```

or in config/szamlazzhu.php file:

``` php
'api_key' => env('SZAMLAZZHU_API_KEY', null),
```

## Usage

WIP

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Testing

``` bash
composer test
```


## Security

If you discover any security-related issues, please email [security@omisai.com](mailto:security@omisai.com) instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
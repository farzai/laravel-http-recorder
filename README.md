# Laravel HTTP Recorder

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farzai/laravel-http-recorder.svg?style=flat-square)](https://packagist.org/packages/farzai/laravel-http-recorder)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/farzai/laravel-http-recorder/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/farzai/laravel-http-recorder/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/farzai/laravel-http-recorder/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/farzai/laravel-http-recorder/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/farzai/laravel-http-recorder.svg?style=flat-square)](https://packagist.org/packages/farzai/laravel-http-recorder)

This package allows you to log all HTTP requests to your Laravel application.

## Installation

You can install the package via composer:

```bash
composer require farzai/laravel-http-recorder
```

### Prepare the database

You need to publish the migration to create the `http_log_requests` table:

```bash
php artisan vendor:publish --tag="http-recorder-migrations"
```

After that, you need to run migrations:
    
```bash
php artisan migrate
```


### Publishing the config file

Publishing the config file is optional:

```bash
php artisan vendor:publish --tag="http-recorder-config"
```

This is the contents of the published config file:

```php
return [
    /*
     * Enable or disable the http recorder.
     */
    'enabled' => env('HTTP_RECORDER_ENABLED', true),

    /*
     * The driver used to store the http logs.
     * It should be a class that implements the `Farzai\HttpRecorder\Contracts\EntryRepositoryInterface`.
     *
     * Supported drivers: "database"
     */
    'driver' => env('HTTP_RECORDER_DRIVER', 'database'),

    /**
     * Process the request in the background.
     * (leave it to empty to use default queue)
     */
    'queue' => env('HTTP_RECORDER_QUEUE'),
    // 'queue' => [
    //     'connection' => env('HTTP_RECORDER_QUEUE_CONNECTION'),
    //     'queue' => env('HTTP_RECORDER_QUEUE_NAME'),
    // ]

    /*
     * Exclude routes from being logged.
     */
    'except' => [
        'urls' => [
            'telescope*',
            'horizon*',
            'nova*',
        ],

        'methods' => [
            'HEAD',
        ],
    ],

    /**
     * Drivers
     */
    'drivers' => [
        'database' => [
            'connection' => env('HTTP_RECORDER_CONNECTION'),
            'table' => env('HTTP_RECORDER_DB_TABLE', 'http_log_requests'),
        ],
    ],

    /**
     * Sensitive request headers and response.
     * 
     * These fields will be replaced with asterisks (*) in the request headers.
     */
    'sensitive' => [
        'headers' => [
            'authorization',
            'x-csrf-token',
            'x-xsrf-token',
            'set-cookie',
        ],
        'body' => [
            'password',
            'password_confirmation',
            'token',
            'access_token',
        ],
    ],

    /**
     * The maximum length of the request body and response body.
     *
     * If the length of the request body or response body exceeds the maximum length,
     * it will be truncated to the maximum length.
     */
    'size_limit' => 64
];
```


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [parsilver](https://github.com/parsilver)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

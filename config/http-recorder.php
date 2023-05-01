<?php

// config for Farzai\HttpRecorder

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
        'url' => [
            'telescope-api*',
            'nova-api*',
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
    'size_limit' => 64,
];

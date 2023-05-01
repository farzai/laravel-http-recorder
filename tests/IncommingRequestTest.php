<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->app['config']->set('http-recorder.enabled', true);

    $this->app['config']->set('http-recorder.drivers.database', [
        'connection' => 'testing',
        'table' => 'http_log_requests',
    ]);
});

it('can log request', function () {
    // Set up routes
    Route::name('example-http-log')
        ->get('/api/example', function () {
            return response()->json(['message' => 'Hello World!']);
        });

    Event::fake([
        \Farzai\HttpRecorder\Events\RequestRecorded::class,
    ]);

    $response = $this->getJson('/api/example');

    $response->assertOk();

    $this->assertDatabaseHas('http_log_requests', [
        'method' => 'GET',
        'uri' => '/api/example',
        'response_status' => 200,
    ]);

    Event::assertDispatched(\Farzai\HttpRecorder\Events\RequestRecorded::class);
});

it('can hide sensitive value', function () {
    // Set up routes
    Route::name('example-http-log')
        ->post('/api/example', function () {
            return response()->json([
                'message' => 'Hello World!',
                'credentials' => [
                    'username' => 'test',
                    'password' => 'thisispassword',
                ],
                'token' => 'thisisatoken',
            ]);
        });

    $response = $this->postJson('/api/example', [
        'username' => 'test',
        'password' => 'thisispassword',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('http_log_requests', [
        'method' => 'POST',
        'uri' => '/api/example',
        'response_status' => 200,
        'response_body' => json_encode([
            'message' => 'Hello World!',
            'credentials' => [
                'username' => 'test',
                'password' => '********',
            ],
            'token' => '********',
        ]),
    ]);

    $this->assertEquals('Hello World!', $response->json('message'));
    $this->assertEquals('test', $response->json('credentials.username'));
    $this->assertEquals('thisispassword', $response->json('credentials.password'));
    $this->assertEquals('thisisatoken', $response->json('token'));
});

it("Should't log request if disabled", function () {
    // Set up routes
    Route::name('example-http-log')
        ->get('/api/example', function () {
            return response()->json(['message' => 'Hello World!']);
        });

    $this->app['config']->set('http-recorder.enabled', false);

    $response = $this->getJson('/api/example');

    $response->assertOk();

    $this->assertDatabaseMissing('http_log_requests', [
        'method' => 'GET',
        'uri' => '/api/example',
        'response_status' => 200,
    ]);
});

it('can exclude routes', function () {
    $this->assertDatabaseMissing('http_log_requests', [
        'method' => 'GET',
        'uri' => '/api/example-exclude',
        'response_status' => 200,
    ]);

    // Set up routes
    Route::name('example-http-log')
        ->get('/api/example-exclude', function () {
            return response()->json(['message' => 'Hello World!']);
        });

    $this->app['config']->set('http-recorder.except.urls', [
        '/api/example-exclude',
    ]);

    $this->assertTrue($this->app['config']->get('http-recorder.enabled'));
    $this->assertEquals([
        '/api/example-exclude',
    ], $this->app['config']->get('http-recorder.except.urls'));

    $response = $this->getJson('/api/example-exclude');

    $response->assertOk();

    $this->assertDatabaseMissing('http_log_requests', [
        'method' => 'GET',
        'uri' => '/api/example-exclude',
        'response_status' => 200,
    ]);
});

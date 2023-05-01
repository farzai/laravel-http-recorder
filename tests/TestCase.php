<?php

namespace Farzai\HttpRecorder\Tests;

use Farzai\HttpRecorder\HttpRecorderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        //
    }

    protected function getPackageProviders($app)
    {
        return [
            HttpRecorderServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_http_log_requests_table.php.stub';
        $migration->up();
    }
}

<?php

namespace Farzai\HttpRecorder;

use Farzai\HttpRecorder\Contracts\EntryRepositoryInterface;
use Farzai\HttpRecorder\Listeners\ProcessLog;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HttpRecorderServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     */
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-http-recorder')
            ->hasConfigFile('http-recorder')
            ->hasMigration('create_http_log_requests_table');
    }

    /**
     * Register any application services.
     */
    public function packageRegistered()
    {
        $this->app['events']->listen(RequestHandled::class, ProcessLog::class);

        $this->app->singleton(RequestRecorder::class, function ($app) {
            return new RequestRecorder($app['config']->get('http-recorder'));
        });

        $this->app->bind(EntryRepositoryInterface::class, function ($app) {
            return (new Factory($app))->driver();
        });
    }
}

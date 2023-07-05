<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('filesystem', function ($app) {
            return new Filesystem(new Local(storage_path('app')));
        });
    }
}

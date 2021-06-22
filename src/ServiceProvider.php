<?php

namespace CustomD\WordFinder;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/word-finder.php';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('word-finder.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'word-finder'
        );

        $this->app->bind('word-finder', function () {
            return new WordFinder(config('word-finder'));
        });
    }
}

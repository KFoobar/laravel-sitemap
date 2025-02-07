<?php

namespace KFoobar\Sitemap;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class SitemapServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/sitemap.php' => config_path('sitemap.php'),
        ], 'config');
    }

    public function register()
    {
        //
    }
}

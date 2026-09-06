<?php

namespace JeffersonGoncalves\Dub\Tests;

use JeffersonGoncalves\Dub\DubServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DubServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('dub.api_key', 'fake-api-key');
        $app['config']->set('dub.api_url', 'https://api.dub.co');
        $app['config']->set('dub.timeout', 5);
    }
}

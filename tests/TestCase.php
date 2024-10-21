<?php

namespace Kirschbaum\Paragon\Tests;

use Illuminate\Contracts\Config\Repository;
use Kirschbaum\Paragon\ParagonServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Symfony\Component\Filesystem\Filesystem;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ParagonServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     */
    protected function defineEnvironment($app): void
    {
        tap($app['config'], function (Repository $config) {
            $config->set('paragon.enums.paths.php', 'Enums');
            $config->set('paragon.enums.paths.ignore', 'Enums/Ignore');
        });
    }

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $filesystem = new Filesystem();

            $filesystem->mirror(__DIR__ . '/Fixtures', app_path(config('paragon.enums.paths.php')));
        });

        $this->beforeApplicationDestroyed(function () {
            $filesystem = new Filesystem();

            $filesystem->remove([
                app_path('Enums'),
                resource_path(config('paragon.enums.paths.generated')),
                resource_path(config('paragon.enums.paths.methods')),
            ]);
        });

        parent::setUp();
    }
}

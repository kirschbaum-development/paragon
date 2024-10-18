<?php

namespace Kirschbaum\Paragon\Tests;

use Kirschbaum\Paragon\ParagonServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ParagonServiceProvider::class,
        ];
    }
}

<?php

namespace Kirschbaum\Paragon\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Kirschbaum\Paragon\ParagonServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ParagonServiceProvider::class,
        ];
    }
}

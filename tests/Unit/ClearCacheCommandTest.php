<?php

use Kirschbaum\Paragon\Commands\ClearCacheCommand;
use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;

it('removes the cache directory', function () {
    setupStatusEnumTestCase($this->app);

    $this->artisan(GenerateEnumsCommand::class);

    $this->artisan(ClearCacheCommand::class);
    expect(storage_path('framework/cache/paragon'))->not->toBeDirectory();
});

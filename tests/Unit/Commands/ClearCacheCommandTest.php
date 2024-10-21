<?php

use Kirschbaum\Paragon\Commands\ClearCacheCommand;
use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;

it('removes the cache directory', function () {
    // Assemble.
    $this->artisan(GenerateEnumsCommand::class);

    // Act.
    $this->artisan(ClearCacheCommand::class);

    // Assert.
    expect(storage_path('framework/cache/paragon'))->not->toBeDirectory();
});

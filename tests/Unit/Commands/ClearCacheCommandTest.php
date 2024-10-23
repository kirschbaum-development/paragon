<?php

use Kirschbaum\Paragon\Commands\ClearCacheCommand;
use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;

exec('composer show pestphp/pest', $output);

if ($output[3] !== 'versions : * v3') {
    return;
}

it('removes the cache directory', function () {
    // Assemble.
    $this->artisan(GenerateEnumsCommand::class);

    // Pre-Assertions;
    expect(storage_path('framework/cache/paragon'))->toBeDirectory();

    // Act.
    $this->artisan(ClearCacheCommand::class);

    // Assert.
    expect(storage_path('framework/cache/paragon'))->not->toBeDirectory();
});

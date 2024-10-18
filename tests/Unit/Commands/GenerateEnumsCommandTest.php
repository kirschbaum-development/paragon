<?php

use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;

it('generates enums', function () {
    // Assemble.
    setupStatusEnumTestCase($this->app);

    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Status.ts'))->toBeFile();
});

it('generates abstract enum', function () {
    // Assemble.
    setupStatusEnumTestCase($this->app);

    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Enum.ts'))->toBeFile();
});

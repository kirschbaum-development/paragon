<?php

use Kirschbaum\Paragon\Commands\MakeEnumMethodCommand;

it('generates enum methods', function () {
    // Assemble.
    setupStatusEnumTestCase($this->app);

    // Act.
    $this->artisan(MakeEnumMethodCommand::class, ['name' => 'asOptions']);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.methods') . DIRECTORY_SEPARATOR . 'asOptions.ts'))->toBeFile();
});

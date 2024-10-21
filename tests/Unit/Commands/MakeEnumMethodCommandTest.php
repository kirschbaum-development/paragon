<?php

use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;
use Kirschbaum\Paragon\Commands\MakeEnumMethodCommand;

it('generates enum methods', function () {
    // Act.
    $this->artisan(MakeEnumMethodCommand::class, ['name' => 'asOptions']);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.methods') . DIRECTORY_SEPARATOR . 'asOptions.ts'))->toBeFile();
});

it('imports the method into the base enum', function () {
    // Act.
    $this->artisan(MakeEnumMethodCommand::class, ['name' => 'asOptions']);
    $this->artisan(GenerateEnumsCommand::class);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Enum.ts'));

    // Assert.
    expect($file)
        ->toContain('asOptions');
});

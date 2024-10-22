<?php

use Kirschbaum\Paragon\Commands\MakeEnumMethodCommand;

it('generates enum methods', function () {
    // Act.
    $this->artisan(MakeEnumMethodCommand::class, ['name' => 'asOptions', '--javascript' => true]);

    $path = resource_path(config('paragon.enums.paths.methods') . DIRECTORY_SEPARATOR . 'asOptions.js');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('export default function asOptions()');
});

it('imports the method into the base enum', function () {
    // Act.
    $this->artisan(MakeEnumMethodCommand::class, ['name' => 'asOptions', '--javascript' => true]);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Enum.js'));

    // Assert.
    expect($file)
        ->toContain('import asOptions from')
        ->toContain('Enum.asOptions = asOptions;');
});

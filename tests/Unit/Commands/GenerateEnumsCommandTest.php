<?php

use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;

it('generates enums', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Status.ts'))->toBeFile();
});

it('generates abstract enum', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Enum.ts'))->toBeFile();
});

it('creates public methods', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Status.ts'));

    // Assert.
    expect($file)
        ->toContain('label');
});

it('ignores methods with \'IgnoreParagon\' attribute', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Status.ts'));

    // Assert.
    expect($file)
        ->not->toContain('ignore');
});

it('ignores static methods', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Status.ts'));

    // Assert.
    expect($file)
        ->not->toContain('cases', 'from', 'ignoreStatic', 'tryFrom');
});

it('ignores enums with \'IgnoreParagon\' attribute', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Ignore.ts'))
        ->not->toBeFile();
});

it('ignores enums within ignored paths', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.generated') . '/Ignore/Ignore.ts'))
        ->not->toBeFile();
});

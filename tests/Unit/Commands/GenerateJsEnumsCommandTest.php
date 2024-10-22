<?php

use App\Enums\IntegerBacked;
use App\Enums\NonBacked;
use App\Enums\StringBacked;
use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;

it('generates string backed enums', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'StringBacked.js');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('import Enum')
        ->not->toContain('type StringBackedDefinition')
        ->toContain('class StringBacked extends Enum')
        ->toContain(StringBacked::Active->name . ': Object.freeze({')
        ->toContain("name: '" . StringBacked::Active->name . "',")
        ->toContain("value: '" . StringBacked::Active->value . "',")
        ->toContain('export default StringBacked;');
});

it('generates number backed enums', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'IntegerBacked.js');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('import Enum')
        ->not->toContain('type IntegerBackedDefinition')
        ->toContain('class IntegerBacked extends Enum')
        ->toContain(IntegerBacked::Active->name . ': Object.freeze({')
        ->toContain("name: '" . IntegerBacked::Active->name . "',")
        ->toContain('value: ' . IntegerBacked::Active->value . ',')
        ->toContain('export default IntegerBacked;');
});

it('generates non-backed enums', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'NonBacked.js');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('import Enum')
        ->not->toContain('type NonBackedDefinition')
        ->toContain('class NonBacked extends Enum')
        ->toContain(NonBacked::Active->name . ': Object.freeze({')
        ->toContain("name: '" . NonBacked::Active->name . "',")
        ->not->toContain('value:')
        ->toContain('export default NonBacked;');
});

it('generates enums recursively', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Nested/Nested.js');

    // Assert.
    expect($path)->toBeFile();
});

it('generates abstract enum', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Enum.js');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('export class ValueError extends Error')
        ->not->toContain('export interface Enumerable')
        ->toContain('class Enum')
        ->toContain('static cases()')
        ->toContain('static from(value)')
        ->toContain('static tryFrom(value)')
        ->toContain('export default Enum;');
});

it('creates public methods', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'StringBacked.js'));

    // Assert.
    expect($file)
        // type definition
        ->not->toContain('label();')
        // items objects
        ->toContain("label: () => '" . StringBacked::Active->label() . "',");
});

it('ignores methods with \'IgnoreParagon\' attribute', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'StringBacked.js'));

    // Assert.
    expect($file)
        ->not->toContain('ignore');
});

it('ignores static methods', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'StringBacked.js'));

    // Assert.
    expect($file)
        ->not->toContain('cases', 'from', 'ignoreStatic', 'tryFrom');
});

it('ignores enums with \'IgnoreParagon\' attribute', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Ignore.js'))
        ->not->toBeFile();
});

it('ignores enums within ignored paths', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

    // Assert.
    expect(resource_path(config('paragon.enums.paths.generated') . '/Ignore/Ignore.js'))
        ->not->toBeFile();
});

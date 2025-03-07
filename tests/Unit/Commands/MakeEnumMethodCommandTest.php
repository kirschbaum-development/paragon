<?php

use App\Enums\StringBacked;
use Kirschbaum\Paragon\Commands\MakeEnumMethodCommand;

it('generates global enum methods', function () {
    // Act.
    $method = 'asOptions';

    $this->artisan(MakeEnumMethodCommand::class, ['name' => $method, '--global' => true]);

    $path = resource_path(config('paragon.enums.paths.methods') . DIRECTORY_SEPARATOR . "{$method}.ts");
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain("export default function {$method}()");
});

it('imports the global method into the base enum', function () {
    // Act.
    $method = 'asOptions';

    $this->artisan(MakeEnumMethodCommand::class, ['name' => $method, '--global' => true]);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Enum.ts'));

    // Assert.
    expect($file)
        ->toContain("import {$method} from")
        ->toContain("Enum.{$method} = {$method};");
});

it('generates enum methods', function () {
    // Act.
    $method = 'asOptions';

    $this->artisan(MakeEnumMethodCommand::class, ['name' => $method, '--enum' => StringBacked::class]);

    $path = (string) Str::of(resource_path(config('paragon.enums.paths.methods')))
        ->append(DIRECTORY_SEPARATOR)
        ->append(Str::replace('\\', '/', StringBacked::class))
        ->append(DIRECTORY_SEPARATOR)
        ->append("{$method}.ts");

    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain("export default function {$method}()");
});

it('imports the method into the enum', function () {
    // Act.
    $method = 'asOptions';
    $enum = class_basename(StringBacked::class);

    $this->artisan(MakeEnumMethodCommand::class, ['name' => $method, '--enum' => StringBacked::class]);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . "{$enum}.ts"));

    // Assert.
    expect($file)
        ->toContain("import {$method} from")
        ->toContain("{$enum}.{$method} = {$method};");
});

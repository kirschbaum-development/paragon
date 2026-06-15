<?php

use App\Enums\EscapedStringBacked;
use App\Enums\IntegerBacked;
use App\Enums\NonBacked;
use App\Enums\StringBacked;
use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;

it('generates string backed enums', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'StringBacked.ts');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('import Enum')
        ->toContain('type StringBackedDefinition')
        ->toContain('class StringBacked extends Enum')
        ->toContain(StringBacked::Active->name . ': Object.freeze({')
        ->toContain("name: '" . StringBacked::Active->name . "',")
        ->toContain('value: ' . json_encode(StringBacked::Active->value) . ',')
        ->toContain('export default StringBacked;');
});

it('generates number backed enums', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'IntegerBacked.ts');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('import Enum')
        ->toContain('type IntegerBackedDefinition')
        ->toContain('class IntegerBacked extends Enum')
        ->toContain(IntegerBacked::Active->name . ': Object.freeze({')
        ->toContain("name: '" . IntegerBacked::Active->name . "',")
        ->toContain('value: ' . IntegerBacked::Active->value . ',')
        ->toContain('export default IntegerBacked;');
});

it('generates non-backed enums', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'NonBacked.ts');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('import Enum')
        ->toContain('type NonBackedDefinition')
        ->toContain('class NonBacked extends Enum')
        ->toContain(NonBacked::Active->name . ': Object.freeze({')
        ->toContain("name: '" . NonBacked::Active->name . "',")
        ->not->toContain('value:')
        ->toContain('export default NonBacked;');
});

it('escapes string values and method return values that need it', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'EscapedStringBacked.ts');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('value: ' . json_encode(EscapedStringBacked::Quote->value) . ',')
        ->toContain('label: (): string => ' . json_encode(EscapedStringBacked::Quote->label()) . ',');
});

it('throws instead of writing broken output for a value that cannot be encoded as JSON', function () {
    // Arrange.
    // Written directly into app_path() rather than tests/Fixtures: GenerateEnumsCommand
    // processes every enum under that path in a single pass, so a fixture there that
    // intentionally fails generation would break every test that runs the command.
    file_put_contents(app_path('Enums/InvalidUtf8Backed.php'), <<<'PHP'
        <?php

        namespace App\Enums;

        enum InvalidUtf8Backed: string
        {
            case Bad = "\xB1\x31";
        }

        PHP);

    // Act.
    $this->artisan(GenerateEnumsCommand::class);
})->throws(JsonException::class);

it('generates enums recursively', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Nested/Nested.ts');

    // Assert.
    expect($path)->toBeFile();
});

it('generates abstract enum', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $path = resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Enum.ts');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain('export class ValueError extends Error')
        ->toContain('export interface Enumerable')
        ->toContain('abstract class Enum implements Enumerable')
        ->toContain('public static cases()')
        ->toContain('public static from(value: number | string)')
        ->toContain('public static tryFrom(value: number | string)')
        ->toContain('export default Enum;');
});

it('creates public methods', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'StringBacked.ts'));

    // Assert.
    expect($file)
        // type definition
        ->toContain('label();')
        // items objects
        ->toContain('label: (): string => ' . json_encode(StringBacked::Active->label()) . ',');
});

it('ignores methods with \'IgnoreParagon\' attribute', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'StringBacked.ts'));

    // Assert.
    expect($file)
        ->not->toContain('ignore');
});

it('ignores static methods', function () {
    // Act.
    $this->artisan(GenerateEnumsCommand::class);

    $file = file_get_contents(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'StringBacked.ts'));

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

describe('command flags and config settings for typescript', function () {
    it('builds typescript by default', function () {
        // Act.
        $this->artisan(GenerateEnumsCommand::class);

        // Assert.
        expect(resource_path(config('paragon.enums.paths.generated') . '/StringBacked.ts'))->toBeFile();
    });

    it('builds javascript via flag', function () {
        // Act.
        $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

        // Assert.
        expect(resource_path(config('paragon.enums.paths.generated') . '/StringBacked.js'))->toBeFile();
    });

    it('builds typescript via flag', function () {
        // Act.
        $this->artisan(GenerateEnumsCommand::class, ['--typescript' => true]);

        // Assert.
        expect(resource_path(config('paragon.enums.paths.generated') . '/StringBacked.ts'))->toBeFile();
    });
});

describe('command flags and config settings for javascript', function () {
    beforeEach(fn () => $this->app['config']->set('paragon.generate-as', 'javascript'));

    it('builds javascript by default', function () {
        // Act.
        $this->artisan(GenerateEnumsCommand::class);

        // Assert.
        expect(resource_path(config('paragon.enums.paths.generated') . '/StringBacked.js'))->toBeFile();
    });

    it('builds typescript via flag', function () {
        // Act.
        $this->artisan(GenerateEnumsCommand::class, ['--typescript' => true]);

        // Assert.
        expect(resource_path(config('paragon.enums.paths.generated') . '/StringBacked.ts'))->toBeFile();
    });

    it('builds javascript via flag', function () {
        // Act.
        $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

        // Assert.
        expect(resource_path(config('paragon.enums.paths.generated') . '/StringBacked.js'))->toBeFile();
    });
});

describe('command flags and config settings exceptions', function () {
    beforeEach(fn () => $this->app['config']->set('paragon.generate-as', 'exception'));

    it('throws exception with bad default', function () {
        // Act.
        $this->artisan(GenerateEnumsCommand::class);
    })->throws(ValueError::class, '"exception" is not a valid backing value for enum');

    it('builds javascript via flag', function () {
        // Act.
        $this->artisan(GenerateEnumsCommand::class, ['--javascript' => true]);

        // Assert.
        expect(resource_path(config('paragon.enums.paths.generated') . '/StringBacked.js'))->toBeFile();
    });

    it('builds typescript via flag', function () {
        // Act.
        $this->artisan(GenerateEnumsCommand::class, ['--typescript' => true]);

        // Assert.
        expect(resource_path(config('paragon.enums.paths.generated') . '/StringBacked.ts'))->toBeFile();
    });
});

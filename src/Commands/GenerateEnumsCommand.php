<?php

namespace Kirschbaum\Paragon\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Kirschbaum\Paragon\Concerns\Builders\EnumBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumJsBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumTsBuilder;
use Kirschbaum\Paragon\Concerns\DiscoverEnums;
use Kirschbaum\Paragon\Concerns\GenerateAs;
use Kirschbaum\Paragon\Concerns\IgnoreParagon;
use Kirschbaum\Paragon\Generators\AbstractEnumGenerator;
use Kirschbaum\Paragon\Generators\EnumGenerator;
use ReflectionEnum;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use UnitEnum;

#[AsCommand(name: 'paragon:enum:generate', description: 'Generate Typescript versions of existing PHP enums')]
class GenerateEnumsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $builder = $this->builder();

        $generatedEnums = $this->enums()
            ->map(fn (string $enum) => app(EnumGenerator::class, ['enum' => $enum, 'builder' => $builder])())
            ->filter();

        $this->components->info("{$generatedEnums->count()} enums have been (re)generated.");

        app(AbstractEnumGenerator::class, ['builder' => $builder])();

        $this->components->info('Abstract enum class has been (re)generated.');

        return self::SUCCESS;
    }

    /**
     * Gather all enum namespaces for searching.
     *
     * @return Collection<int,class-string<UnitEnum>>
     */
    protected function enums(): Collection
    {
        /** @var string */
        $phpPath = config('paragon.enums.paths.php');

        return DiscoverEnums::within(app_path($phpPath))
            ->reject(function (string $enum) {
                if (! enum_exists($enum)) {
                    return true;
                }

                $reflector = new ReflectionEnum($enum);

                $pathsToIgnore = Arr::map(
                    Arr::wrap(config('paragon.enums.paths.ignore')),
                    fn (string $path): string => Str::finish(app_path($path), '/'),
                );

                return $reflector->getAttributes(IgnoreParagon::class)
                    || Str::startsWith((string) $reflector->getFileName(), $pathsToIgnore);
            })
            ->values();
    }

    protected function builder(): EnumBuilder
    {
        /** @var string */
        $generateAs = config('paragon.generate-as');

        $builder = match (true) {
            $this->option('javascript') => EnumJsBuilder::class,
            $this->option('typescript') => EnumTsBuilder::class,
            default => GenerateAs::from($generateAs)->builder()
        };

        return app($builder);
    }

    /**
     * Get the console command options.
     *
     * @return array<int, InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption(
                name: 'javascript',
                shortcut: 'j',
                mode: InputOption::VALUE_NONE,
                description: 'Output Javascript files',
            ),
            new InputOption(
                name: 'typescript',
                shortcut: 't',
                mode: InputOption::VALUE_NONE,
                description: 'Output TypeScript files',
            ),
        ];
    }
}

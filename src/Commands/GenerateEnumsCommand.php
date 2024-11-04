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
            ->map(fn (ReflectionEnum $enum) => app(EnumGenerator::class, ['enum' => $enum, 'builder' => $builder])())
            ->filter();

        $this->components->info("{$generatedEnums->count()} enums have been (re)generated.");

        app(AbstractEnumGenerator::class, ['builder' => $builder])();

        $this->components->info('Abstract enum class has been (re)generated.');

        return self::SUCCESS;
    }

    /**
     * Gather all enum namespaces for searching.
     *
     * @return Collection<int, ReflectionEnum>
     */
    protected function enums(): Collection
    {
        return DiscoverEnums::within(app_path(config()->string('paragon.enums.paths.php')))
            ->reject(function (ReflectionEnum $enum) {
                $paths = Arr::map(
                    Arr::wrap(config('paragon.enums.paths.ignore')),
                    fn (string $path): string => Str::finish(app_path($path), '/'),
                );

                return $enum->getAttributes(IgnoreParagon::class)
                    || Str::startsWith((string) $enum->getFileName(), $paths);
            })
            ->values();
    }

    protected function builder(): EnumBuilder
    {
        $generateAs = config()->string('paragon.generate-as');

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

<?php

namespace Kirschbaum\Paragon\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Kirschbaum\Paragon\Concerns\Builders\EnumBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumJsBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumTsBuilder;
use Kirschbaum\Paragon\Concerns\DiscoverEnums;
use Kirschbaum\Paragon\Concerns\HasCommandLineOptions;
use Kirschbaum\Paragon\Concerns\IgnoreParagon;
use Kirschbaum\Paragon\Generators\AbstractEnumGenerator;
use Kirschbaum\Paragon\Generators\EnumGenerator;
use ReflectionEnum;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'paragon:generate-enums', description: 'Generate Typescript/Javascript versions of existing PHP enums')]
class GenerateEnumsCommand extends Command
{
    use HasCommandLineOptions;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $builder = $this->builder();

            $generatedEnums = $this->enums()
                ->map(fn ($enum) => app(EnumGenerator::class, ['enum' => $enum, 'builder' => $builder])())
                ->filter();
        } catch (Exception $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("{$generatedEnums->count()} enums have been (re)generated.");

        app(AbstractEnumGenerator::class, ['builder' => $builder])();

        $this->components->info('Abstract enum class has been (re)generated.');

        return self::SUCCESS;
    }

    /**
     * Gather all enum namespaces for searching.
     *
     * @return Collection<int,class-string<\UnitEnum>>
     */
    protected function enums(): Collection
    {
        return DiscoverEnums::within(app_path(config()->string('paragon.enums.paths.php')))
            ->reject(function ($enum) {
                if (! enum_exists($enum)) {
                    return true;
                }

                $reflector = new ReflectionEnum($enum);

                $paths = Arr::map(
                    Arr::wrap(config('paragon.enums.paths.ignore')),
                    fn (string $path): string => Str::finish(app_path($path), '/'),
                );

                return $reflector->getAttributes(IgnoreParagon::class)
                    || Str::startsWith((string) $reflector->getFileName(), $paths);
            })
            ->values();
    }

    protected function builder(): EnumBuilder
    {
        return $this->option('javascript')
            ? app(EnumJsBuilder::class)
            : app(EnumTsBuilder::class);
    }
}

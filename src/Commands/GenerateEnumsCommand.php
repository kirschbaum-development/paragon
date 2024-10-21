<?php

namespace Kirschbaum\Paragon\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Kirschbaum\Paragon\Concerns\DiscoverEnums;
use Kirschbaum\Paragon\Concerns\IgnoreParagon;
use Kirschbaum\Paragon\Generators\AbstractEnumGenerator;
use Kirschbaum\Paragon\Generators\EnumGenerator;
use ReflectionEnum;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'paragon:generate-enums', description: 'Generate Typescript versions of existing PHP enums')]
class GenerateEnumsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $generatedEnums = $this->enums()
            ->map(fn ($enum) => app(EnumGenerator::class, ['enum' => $enum])())
            ->filter();

        $this->components->info("{$generatedEnums->count()} enums have been (re)generated.");

        app(AbstractEnumGenerator::class)();

        $this->components->info('Abstract enum class has been (re)generated.');

        return self::SUCCESS;
    }

    /**
     * Gather all enum namespaces for searching.
     *
     * @return Collection<int, non-falsy-string>
     */
    protected function enums(): Collection
    {
        return DiscoverEnums::within(app_path(config('paragon.enums.paths.php')))
            ->reject(function ($enum) {
                $reflector = new ReflectionEnum($enum);

                $paths = Arr::map(Arr::wrap(config('paragon.enums.paths.ignore')), function ($path) {
                    return Str::finish(app_path($path), '/');
                });

                return $reflector->getAttributes(IgnoreParagon::class)
                    || Str::startsWith($reflector->getFileName(), $paths);
            })
            ->values();
    }
}

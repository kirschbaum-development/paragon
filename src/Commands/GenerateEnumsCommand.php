<?php

namespace Kirschbaum\Paragon\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Kirschbaum\Paragon\Concerns\DiscoverEnums;
use Kirschbaum\Paragon\Generators\AbstractEnumGenerator;
use Kirschbaum\Paragon\Generators\EnumGenerator;
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
     */
    protected function enums(): Collection
    {
        return DiscoverEnums::within(config('paragon.enums.paths.php'))
            ->values();
    }
}

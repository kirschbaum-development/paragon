<?php

namespace Kirschbaum\Paragon\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Kirschbaum\Paragon\Concerns\DiscoverEnums;
use Kirschbaum\Paragon\Generators\AbstractEnumGenerator;
use Kirschbaum\Paragon\Generators\EventGenerator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'paragon:generate-events', description: 'Generate Typescript versions of existing Laravel Events')]
class GenerateEventsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        app(EventGenerator::class)();

//        $this->components->info("{$generatedEnums->count()} enums have been (re)generated.");

//        app(AbstractEnumGenerator::class)();

//        $this->components->info('Abstract enum class has been (re)generated.');

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

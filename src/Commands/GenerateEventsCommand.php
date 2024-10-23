<?php

namespace Kirschbaum\Paragon\Commands;

use Exception;
use Illuminate\Console\Command;
use Kirschbaum\Paragon\Concerns\DiscoverBroadcastEvents;
use Kirschbaum\Paragon\Generators\EventGenerator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'paragon:generate-events', description: 'Generate Javascript versions of existing Laravel Events')]
class GenerateEventsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $events = DiscoverBroadcastEvents::within(app_path(config()->string('paragon.events.paths.php')));

            app(EventGenerator::class, ['events' => $events])();
        } catch (Exception $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("{$events->count()} events have been (re)generated.");

        return self::SUCCESS;
    }
}

<?php

namespace Kirschbaum\Paragon\Commands;

use Exception;
use Illuminate\Console\Command;
use Kirschbaum\Paragon\Concerns\DiscoverBroadcastEvents;
use Kirschbaum\Paragon\Concerns\HasCommandLineOptions;
use Kirschbaum\Paragon\Generators\EventGenerator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'paragon:generate-broadcast-events', description: 'Generate Typescript/Javascript definitions for Laravel Broadcast Events')]
class GenerateBroadcastEventsCommand extends Command
{
    use HasCommandLineOptions;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $events = DiscoverBroadcastEvents::within(app_path(config()->string('paragon.events.paths.php')));

            app(EventGenerator::class, ['events' => $events, 'generateJavascript' => $this->option('javascript')])();
        } catch (Exception $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("{$events->count()} events have been (re)generated.");

        return self::SUCCESS;
    }
}

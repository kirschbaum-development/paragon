<?php

namespace Kirschbaum\Paragon\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Kirschbaum\Paragon\Concerns\DiscoverBroadcastEvents;
use Kirschbaum\Paragon\Generators\EventGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'paragon:event:generate', description: 'Generate Typescript/Javascript definitions for Laravel Broadcast Events')]
class GenerateBroadcastEventsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $config = Arr::wrap(config('paragon.events.paths.php'));

            /**
             * @var list<string> $paths
             */
            $paths = collect($config)
                ->map(fn (string $path): string => app_path($path))
                ->toArray();

            $events = DiscoverBroadcastEvents::within($paths);

            app(EventGenerator::class, ['events' => $events, 'generateJavascript' => $this->option('javascript')])();
        } catch (Exception $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("{$events->count()} events have been (re)generated.");

        return self::SUCCESS;
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
        ];
    }
}

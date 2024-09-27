<?php

namespace Kirschbaum\Paragon\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'paragon:clear', description: 'Remove the cached Paragon files')]
class ClearCacheCommand extends Command
{
    protected Filesystem $cache;

    /**
     * Create a new test clear command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->cache = Storage::createLocalDriver([
            'root' => storage_path('framework/cache'),
        ]);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->cache->deleteDirectory('paragon');

        $this->components->info('Paragon cache cleared successfully.');

        return self::SUCCESS;
    }
}

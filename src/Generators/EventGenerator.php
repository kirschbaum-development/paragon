<?php

namespace Kirschbaum\Paragon\Generators;

use const JSON_PRETTY_PRINT;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class EventGenerator
{
    protected Filesystem $cache;

    protected Filesystem $files;

    /**
     * Create new EventGenerator instance.
     */
    public function __construct(protected Collection $events)
    {
        $this->files = Storage::createLocalDriver([
            'root' => resource_path(config('paragon.events.paths.generated')),
        ]);
    }

    public function __invoke(): bool
    {
        $this->files->put($this->path(), $this->contents());

        return true;
    }

    /**
     * Typescript event file contents.
     */
    protected function contents(): string
    {
        $content = $this->events
            ->mapWithKeys(fn ($value, $key) => [str($value)->replace('\\', '.')->toString() => $value])
            ->undot();

        return str(file_get_contents($this->stubPath()))
            ->replace('{{ Events }}', json_encode($content, JSON_PRETTY_PRINT));
    }

    /**
     * Get the path to the stubs.
     */
    public function stubPath(): string
    {
        return __DIR__ . '/../../stubs/event.stub';
    }

    /**
     * Path where the events will be saved.
     */
    protected function path(): string
    {
        return str('Events.ts');
    }
}

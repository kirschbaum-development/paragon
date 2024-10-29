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
    public function __construct(protected Collection $events, protected bool $generateJavascript = false)
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
        $object = $this->events
            ->mapWithKeys(fn ($value, $key) => [str($value)->replace('\\', '.')->toString() => $value])
            ->undot()
            ->toJson(JSON_PRETTY_PRINT);

        $interface = $this->events
            ->map(function ($path) {
                $parts = explode('\\', $path);
                array_pop($parts);

                return implode('\\', $parts);
            })
            ->mapWithKeys(fn ($value, $key) => [str($value)->replace('\\', '.')->toString() => ['[key:string]' => 'string | { [subKey: string]: string }']])
            ->undot()
            ->toJson(JSON_PRETTY_PRINT);

        return str(file_get_contents($this->stubPath()))
            ->replace('{{ Interface }}', str($interface)->replace('"', ''))
            ->replace('{{ Events }}', $object);
    }

    /**
     * Get the path to the stubs.
     */
    public function stubPath(): string
    {
        return $this->generateJavascript
            ? __DIR__ . '/../../stubs/event-js.stub'
            : __DIR__ . '/../../stubs/event-ts.stub';
    }

    /**
     * Path where the events will be saved.
     */
    protected function path(): string
    {
        return $this->generateJavascript
            ? str('Events.js')
            : str('Events.ts');
    }
}

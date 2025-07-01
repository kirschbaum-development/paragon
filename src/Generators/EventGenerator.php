<?php

namespace Kirschbaum\Paragon\Generators;

use const JSON_PRETTY_PRINT;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;

class EventGenerator
{
    protected Filesystem $cache;

    protected Filesystem $files;

    /**
     * Create a new EventGenerator instance.
     *
     * @param  Collection<string, class-string>  $events
     */
    public function __construct(protected Collection $events, protected bool $generateJavascript = false)
    {
        /**
         * @var string $path
         */
        $path = config('paragon.events.paths.generated');

        $this->files = Storage::createLocalDriver([
            'root' => resource_path($path),
        ]);
    }

    public function __invoke(): bool
    {
        $this->files->put($this->path(), $this->contents());

        return true;
    }

    /**
     * TypeScript event file contents.
     */
    protected function contents(): string
    {
        return str(file_get_contents($this->stubPath()) ?: null)
            ->replace('{{ Events }}', $this->events() ?? '')
            ->replace('{{ Interface }}', $this->interface() ?? '');
    }

    /**
     * Determine the broadcastable event names.
     */
    protected function events(): ?string
    {
        $events = $this->events
            ->map(function ($value) {
                $reflection = new ReflectionClass($value);

                if ($reflection->hasMethod('broadcastAs')) {
                    $instance = $reflection->newInstanceWithoutConstructor();
                    $method = $reflection->getMethod('broadcastAs');

                    /**
                     * @var string $name
                     */
                    $name = $method->invoke($instance);
                }

                $name ??= $value;

                return ".{$name}";
            })
            ->undot()
            ->toJson(JSON_PRETTY_PRINT);

        return preg_replace('/"([^"]+)":/', '$1:', $events);
    }

    /**
     * Determine the TypeScript event interface.
     */
    protected function interface(): ?string
    {
        $interface = $this->events
            ->map(fn () => 'string')
            ->undot()
            ->toJson(JSON_PRETTY_PRINT);

        return str($interface)->replace('"', '');
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

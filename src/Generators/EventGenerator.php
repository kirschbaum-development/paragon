<?php

namespace Kirschbaum\Paragon\Generators;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Kirschbaum\Paragon\Concerns\DiscoverEvents;
use ReflectionEnum;
use ReflectionException;
use const JSON_PRETTY_PRINT;

class EventGenerator
{
    /**
     * Line prefix for ensuring proper file formatting.
     */
    protected string $linePrefix = PHP_EOL . '            ';

    protected Filesystem $cache;

    protected Filesystem $files;

    protected ReflectionEnum $reflector;

    /**
     * Create new EnumGenerator instance.
     *
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->files = Storage::createLocalDriver([
            'root' => resource_path(config('paragon.events.paths.generated')),
        ]);

//        $this->cache = Storage::createLocalDriver([
//            'root' => storage_path('framework/cache/paragon'),
//        ]);

//        $this->reflector = new ReflectionEnum($this->enum);
    }

    public function __invoke(): bool
    {
        if ($this->generatedFileExists())
//            && $this->cached())
        {
            return false;
        }

        $this->files->put($this->path(), $this->contents());

//        $this->cacheEvents();

        return true;
    }

    /**
     * Typescript event file contents.
     */
    protected function contents(): string
    {
        $events = DiscoverEvents::within('app/Events');

        $content = $events
            ->mapWithKeys(function ($value, $key) {
                return [str($key)->replace("\\", ".")->toString() => $value];
            })
            // filter out anything that doesn't implement shouldbroadcast
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

    protected function generatedFileExists(): bool
    {
        return $this->files->exists($this->path());
    }

    /*
    |--------------------------------------------------------------------------
    | Enum Caching
    |--------------------------------------------------------------------------
    */

    protected function cached(): bool
    {
        return $this->cache->get($this->cacheFilename()) === $this->cachedFile();
    }

    protected function cacheFilename(): string
    {
        return md5($this->reflector->getFileName());
    }

    protected function cachedFile(): string
    {
        return md5_file($this->reflector->getFileName());
    }

    protected function cacheEnum(): void
    {
        $this->cache->put($this->cacheFilename(), $this->cachedFile());
    }
}

<?php

namespace Kirschbaum\Paragon\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class DiscoverEvents
{
    /**
     * Get all the events by searching the given directory.
     */
    public static function within(array|string $path): Collection
    {
        return static::getEvents(Finder::create()->files()->in($path));
    }

    /**
     * Filter the files down to only events.
     */
    protected static function getEvents($files): Collection
    {
        return collect($files)
            ->mapWithKeys(function ($file) {
                try {
                    $reflector = new ReflectionClass($event = static::classFromFile($file));
                } catch (ReflectionException) {
                    return [];
                }

                return $reflector->isInstantiable()
                    ? [$event => $event]
                    : [];
            })
            ->filter();
    }

    /**
     * Extract the class name from the given file path.
     */
    protected static function classFromFile(SplFileInfo $file): string
    {
        $class = trim(Str::replaceFirst(base_path(), '', $file->getRealPath()), DIRECTORY_SEPARATOR);

        return str_replace(
            [DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())) . '\\'],
            ['\\', app()->getNamespace()],
            ucfirst(Str::replaceLast('.php', '', $class))
        );
    }
}

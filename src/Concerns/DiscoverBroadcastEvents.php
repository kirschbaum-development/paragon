<?php

namespace Kirschbaum\Paragon\Concerns;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;

class DiscoverBroadcastEvents
{
    use CanGetClassFromFile;

    /**
     * Get all the events by searching the given directory.
     */
    public static function within(array|string $path): Collection
    {
        return static::getBroadcastEvents(Finder::create()->files()->in($path));
    }

    /**
     * Filter the files down to only concrete classes that implement ShouldBroadcast.
     */
    protected static function getBroadcastEvents($files): Collection
    {
        return collect($files)
            ->mapWithKeys(function ($file) {
                try {
                    $reflector = new ReflectionClass($event = static::classFromFile($file));
                } catch (ReflectionException) {
                    return [];
                }

                return $reflector->isInstantiable()
                    && $reflector->implementsInterface('Illuminate\Contracts\Broadcasting\ShouldBroadcast')
                    ? [$file->getRealPath() => $event]
                    : [];
            })
            ->sort()
            ->filter();
    }
}

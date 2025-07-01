<?php

namespace Kirschbaum\Paragon\Concerns;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Collection;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DiscoverBroadcastEvents
{
    /**
     * Get all the events by searching the given directory.
     *
     * @param  string|list<string>  $paths
     *
     * @return Collection<string, class-string>
     */
    public static function within(array|string $paths): Collection
    {
        return static::getBroadcastEvents(Finder::create()->files()->in($paths));
    }

    /**
     * Filter the files down to only concrete classes that implement ShouldBroadcast.
     *
     * @param  Finder<string, SplFileInfo>  $files
     *
     * @return Collection<string, class-string>
     */
    protected static function getBroadcastEvents(Finder $files): Collection
    {
        /**
         * @var Collection<int, SplFileInfo> $fileCollection
         */
        $fileCollection = collect($files);

        return $fileCollection
            ->mapWithKeys(function (SplFileInfo $file) {
                $event = static::classFromFile($file);

                if (! $event) {
                    return [];
                }

                $reflector = new ReflectionClass($event);

                if (
                    $reflector->isInstantiable()
                    && $reflector->implementsInterface(ShouldBroadcast::class)
                ) {
                    $key = (string) str($event)
                        ->remove(['App\\Events\\', 'App\\'])
                        ->replace('\\', '.');

                    return [$key => $event];
                }

                return [];
            })
            ->filter()
            ->sort();
    }

    /**
     * Extract the class name from the given file path.
     *
     * @return class-string|false
     */
    protected static function classFromFile(SplFileInfo $file): string|false
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            return false;
        }

        $namespace = null;
        $class = null;

        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^namespace\s+([^;]+);/', $line, $matches)) {
                $namespace = $matches[1];
            }

            if (preg_match('/^class\s+(\w+)(?:\s*:\s*\w+)?/', $line, $matches)) {
                $class = $matches[1];
            }

            if (
                ($namespace && $class)
                || preg_match('/\b(enum|trait|interface)\b/', $line)
            ) {
                break;
            }
        }

        fclose($handle);

        if ($namespace && $class) {
            /**
             * @var class-string $className
             */
            $className = "{$namespace}\\{$class}";

            return $className;
        }

        return false;
    }
}

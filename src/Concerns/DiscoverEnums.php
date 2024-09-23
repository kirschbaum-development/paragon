<?php

namespace Kirschbaum\Paragon\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class DiscoverEnums
{
    /**
     * Get all the enums by searching the given directory.
     */
    public static function within(array|string $path): Collection
    {
        return static::getEnums(Finder::create()->files()->in($path));
    }

    /**
     * Filter the files down to only enums.
     */
    protected static function getEnums($files): Collection
    {
        return collect($files)
            ->mapWithKeys(function ($file) {
                try {
                    $reflector = new ReflectionClass($enum = static::classFromFile($file));
                } catch (ReflectionException) {
                    return [];
                }

                return $reflector->isEnum()
                    ? [$enum => $enum]
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

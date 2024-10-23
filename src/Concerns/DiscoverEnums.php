<?php

namespace Kirschbaum\Paragon\Concerns;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use UnitEnum;

class DiscoverEnums
{
    /**
     * Get all the enums by searching the given directory.
     *
     * @param  array<int, string>|string  $path
     *
     * @return Collection<class-string<UnitEnum>, class-string<UnitEnum>>
     */
    public static function within(array|string $path): Collection
    {
        return static::getEnums(Finder::create()->files()->in($path));
    }

    /**
     * Filter the files down to only enums.
     *
     * @param  Finder<string, SplFileInfo>  $files
     *
     * @return Collection<class-string<UnitEnum>, class-string<UnitEnum>>
     */
    protected static function getEnums(Finder $files): Collection
    {
        /**
         * @var Collection<int, SplFileInfo> $fileCollection
         */
        $fileCollection = collect($files);

        return $fileCollection
            ->mapWithKeys(function (SplFileInfo $file) {
                try {
                    if (! class_exists($enum = static::classFromFile($file))) {
                        return [];
                    }

                    $reflector = new ReflectionClass($enum);
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
     *
     * @return class-string<UnitEnum>
     */
    protected static function classFromFile(SplFileInfo $file): string
    {
        /**
         * @var class-string<UnitEnum>
         */
        return str($file->getRealPath())
            ->replaceFirst(base_path(), '')
            ->trim(DIRECTORY_SEPARATOR)
            ->replaceLast('.php', '')
            ->ucfirst()
            ->replace(
                search: [DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())) . '\\'],
                replace: ['\\', app()->getNamespace()]
            )
            ->toString();
    }
}

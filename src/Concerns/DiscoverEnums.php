<?php

namespace Kirschbaum\Paragon\Concerns;

use Illuminate\Support\Collection;
use ReflectionEnum;
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
     * @return Collection<int, ReflectionEnum>
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
     * @return Collection<int, ReflectionEnum>
     */
    protected static function getEnums(Finder $files): Collection
    {
        /**
         * @var Collection<int, SplFileInfo> $fileCollection
         */
        $fileCollection = collect($files);

        return $fileCollection
            ->map(function (SplFileInfo $file) {
                try {
                    return static::classFromFile($file);
                } catch (ReflectionException) {
                    return false;
                }
            })
            ->filter(fn ($enum) => $enum instanceof ReflectionEnum);
    }

    /**
     * Extract the class name from the given file path.
     *
     * @throws ReflectionException
     */
    protected static function classFromFile(SplFileInfo $file): ReflectionEnum|false
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            return false;
        }

        $namespace = null;
        $enumClass = null;

        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^namespace\s+([^;]+);/', $line, $matches)) {
                $namespace = $matches[1];
            }

            if (preg_match('/^enum\s+(\w+)(?:\s*:\s*\w+)?/', $line, $matches)) {
                $enumClass = $matches[1];
            }

            if (
                ($namespace && $enumClass)
                || preg_match('/\b(class|trait|interface)\b/', $line)
            ) {
                break;
            }
        }

        fclose($handle);

        /**
         * @var class-string<UnitEnum>|false $enum
         */
        $enum = $namespace && $enumClass
            ? "{$namespace}\\{$enumClass}"
            : false;

        return $enum ? new ReflectionEnum($enum) : false;
    }
}

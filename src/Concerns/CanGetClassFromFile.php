<?php

namespace Kirschbaum\Paragon\Concerns;

use const DIRECTORY_SEPARATOR;

use SplFileInfo;

trait CanGetClassFromFile
{
    /**
     * Extract the class name from the given file path.
     */
    protected static function classFromFile(SplFileInfo $file): string
    {
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

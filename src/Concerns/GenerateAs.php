<?php

namespace Kirschbaum\Paragon\Concerns;

use Kirschbaum\Paragon\Concerns\Builders\EnumBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumJsBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumTsBuilder;

enum GenerateAs: string
{
    case Javascript = 'javascript';

    case TypeScript = 'typescript';

    /**
     * @return class-string<EnumBuilder>
     */
    public function builder(): string
    {
        return match ($this) {
            self::Javascript => EnumJsBuilder::class,
            self::TypeScript => EnumTsBuilder::class,
        };
    }
}

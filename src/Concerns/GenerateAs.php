<?php

namespace Kirschbaum\Paragon\Concerns;

enum GenerateAs: string
{
    case Javascript = 'javascript';

    case TypeScript = 'typescript';
}

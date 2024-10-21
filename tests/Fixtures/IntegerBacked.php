<?php

namespace App\Enums;

enum IntegerBacked: int
{
    case Active = 1;
    case Inactive = 0;

    public function label(): string
    {
        return 'label';
    }
}

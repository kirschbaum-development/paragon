<?php

namespace App\Enums;

enum NonBacked
{
    case Active;
    case Inactive;

    public function label(): string
    {
        return 'label';
    }
}

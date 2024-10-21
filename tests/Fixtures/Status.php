<?php

namespace App\Enums;

use Kirschbaum\Paragon\Concerns\IgnoreParagon;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return 'label';
    }

    #[IgnoreParagon]
    public function ignore(): string
    {
        return 'ignore';
    }

    public static function ignoreStatic(): string
    {
        return 'ignore';
    }
}

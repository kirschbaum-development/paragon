<?php

namespace App\Enums;

use Kirschbaum\Paragon\Concerns\IgnoreParagon;

#[IgnoreParagon]
enum Ignore: string
{
    case Ignore = 'ignore';
}

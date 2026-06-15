<?php

namespace App\Enums;

enum EscapedStringBacked: string
{
    case Quote = 'it\'s a "test"';

    public function label(): string
    {
        return 'can\'t "stop"';
    }
}

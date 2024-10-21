<?php

use Kirschbaum\Paragon\Concerns\IgnoreParagon;

arch()->expect(IgnoreParagon::class)
    ->toExtendNothing();

arch()->expect(IgnoreParagon::class)
    ->toHaveAttribute(Attribute::class);

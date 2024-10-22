<?php

use Kirschbaum\Paragon\Concerns\IgnoreParagon;

arch('IgnoreParagon::Should extend nothing')->expect(IgnoreParagon::class)
    ->toExtendNothing();

arch('IgnoreParagon::Should have attribute')->expect(IgnoreParagon::class)
    ->toHaveAttribute(Attribute::class);

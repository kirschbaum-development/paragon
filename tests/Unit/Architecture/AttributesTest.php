<?php

use Attribute;
use Kirschbaum\Paragon\Concerns\IgnoreWhenGeneratingTypescript;

arch()->expect(IgnoreWhenGeneratingTypescript::class)
    ->toExtendNothing();

arch()->expect(IgnoreWhenGeneratingTypescript::class)
    ->toHaveAttribute(Attribute::class);

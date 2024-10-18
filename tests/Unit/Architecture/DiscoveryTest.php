<?php

use Kirschbaum\Paragon\Concerns\DiscoverEnums;

arch()->expect(DiscoverEnums::class)
    ->toExtendNothing();

arch()->expect(DiscoverEnums::class)
    ->not->toHavePrivateMethodsBesides(['within']);

arch()->expect(DiscoverEnums::class)
    ->not->toHavePublicMethodsBesides(['within']);

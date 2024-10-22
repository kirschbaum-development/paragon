<?php

use Kirschbaum\Paragon\Concerns\DiscoverEnums;

arch('DiscoverEnums should extend nothing')->expect(DiscoverEnums::class)
    ->toExtendNothing();

arch('DiscoverEnums should not have private methods')->expect(DiscoverEnums::class)
    ->not->toHavePrivateMethodsBesides(['within']);

arch('DiscoverEnums should not have public methods')->expect(DiscoverEnums::class)
    ->not->toHavePublicMethodsBesides(['within']);

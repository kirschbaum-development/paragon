<?php

use Kirschbaum\Paragon\Concerns\DiscoverEnums;

arch('DiscoverEnums should extend nothing')->expect(DiscoverEnums::class)
    ->toExtendNothing();

exec('composer show pestphp/pest', $output);

if ($output[3] === 'versions : * v3') {
    arch('DiscoverEnums should not have private methods')->expect(DiscoverEnums::class)
        ->not->toHavePrivateMethodsBesides(['within']);

    arch('DiscoverEnums should not have public methods')->expect(DiscoverEnums::class)
        ->not->toHavePublicMethodsBesides(['within']);
}

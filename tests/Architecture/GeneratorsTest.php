<?php

arch('Generators should have suffix')->expect('Kirschbaum\Paragon\Generators')
    ->classes()
    ->toHaveSuffix('Generator');

arch('Generators should extend nothing')->expect('Kirschbaum\Paragon\Generators')
    ->toExtendNothing();

arch('Generators should be invokable')->expect('Kirschbaum\Paragon\Generators')
    ->toBeInvokable();

arch('Generators should not have private methods')->expect('Kirschbaum\Paragon\Generators')
    ->not->toHavePrivateMethodsBesides(['__construct', '__invoke']);

arch('Generators should not have public methods')->expect('Kirschbaum\Paragon\Generators')
    ->not->toHavePublicMethodsBesides(['__construct', '__invoke']);

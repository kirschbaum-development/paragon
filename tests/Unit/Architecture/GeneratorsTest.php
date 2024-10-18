<?php

arch()->expect('Kirschbaum\Paragon\Generators')
    ->classes()
    ->toHaveSuffix('Generator');

arch()->expect('Kirschbaum\Paragon\Generators')
    ->toExtendNothing();

arch()->expect('Kirschbaum\Paragon\Generators')
    ->toBeInvokable();

arch()->expect('Kirschbaum\Paragon\Generators')
    ->not->toHavePrivateMethodsBesides(['__construct', '__invoke']);

arch()->expect('Kirschbaum\Paragon\Generators')
    ->not->toHavePublicMethodsBesides(['__construct', '__invoke']);

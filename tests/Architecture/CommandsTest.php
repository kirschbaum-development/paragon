<?php

use Symfony\Component\Console\Attribute\AsCommand;

arch()->expect('Kirschbaum\Paragon\Commands')
    ->classes()
    ->toHaveSuffix('Command');

arch()->expect('Kirschbaum\Paragon\Commands')
    ->toHaveAttribute(AsCommand::class);

arch()->expect('Kirschbaum\Paragon\Commands')
    ->not->toHavePrivateMethodsBesides(['__construct', 'handle']);

arch()->expect('Kirschbaum\Paragon\Commands')
    ->not->toHavePublicMethodsBesides(['__construct', 'handle']);

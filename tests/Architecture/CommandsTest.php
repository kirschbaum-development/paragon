<?php

use Symfony\Component\Console\Attribute\AsCommand;

arch('Commands should have suffix')->expect('Kirschbaum\Paragon\Commands')
    ->classes()
    ->toHaveSuffix('Command');

arch('Commands should have attribute')->expect('Kirschbaum\Paragon\Commands')
    ->toHaveAttribute(AsCommand::class);

exec('composer show pestphp/pest', $output);

if ($output[3] === 'versions : * v3') {
    arch('Commands should not have private methods')->expect('Kirschbaum\Paragon\Commands')
        ->not->toHavePrivateMethodsBesides(['__construct', 'handle']);

    arch('Commands should not have public methods')->expect('Kirschbaum\Paragon\Commands')
        ->not->toHavePublicMethodsBesides(['__construct', 'handle']);
}

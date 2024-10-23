<?php

// Pest Presets are available beginning in version 3.
exec('composer show pestphp/pest', $output);

if ($output[3] === 'versions : * v3') {
    arch('PHP preset')->preset()->php();
}

// Security without the md5 method as we need it for caching.
arch('Not to use vulnerable functions')
    ->expect([
        'array_rand',
        'assert',
        'create_function',
        'dl',
        'eval',
        'exec',
        'extract',
        'mb_parse_str',
        'mt_rand',
        'parse_str',
        'passthru',
        'rand',
        'sha1',
        'shell_exec',
        'shuffle',
        'str_shuffle',
        'system',
        'tempnam',
        'uniqid',
        'unserialize',
    ])
    ->not->toBeUsed();

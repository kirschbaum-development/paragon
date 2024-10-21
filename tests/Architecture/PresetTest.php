<?php

arch()->preset()->php();

// Security without the md5 method as we need it for caching.
arch()
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

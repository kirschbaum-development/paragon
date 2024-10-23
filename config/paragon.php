<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Generation Language
    |--------------------------------------------------------------------------
    |
    | Here you may specify the language that Paragon will use when generating files. The default
    | is TypeScript which allows for type hinting in IDE's while also providing strong typing.
    | If your project doesn't support TypeScript you may instead change this to Javascript.
    |
    | Please note you may use the `--javascript` or `-j` flag as well if you need to generate Javascript.
    |
    | Supported: "typescript", "javascript"
    |
    */

    'generate-as' => 'typescript',

    /*
    |--------------------------------------------------------------------------
    | Paragon Enums
    |--------------------------------------------------------------------------
    |
    | Here you may specify the settings for enum code generation. You have the ability to change
    | file paths for locating php enums, what to ignore, and where the generated files should
    | be placed. By default, Paragon will look in your entire app/ directory for all enums.
    |
    */

    'enums' => [
        'abstract-class' => 'Enum',

        'paths' => [
            'php' => '',
            'ignore' => [],
            'generated' => 'js/enums',
            'methods' => 'js/vendors/paragon/enums',
        ],
    ],
];

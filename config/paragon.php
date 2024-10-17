<?php

return [
    'enums' => [
        'abstract-class' => 'Enum',

        'paths' => [
            'php' => app_path(),
            'generated' => 'js/enums',
            'methods' => 'js/vendors/paragon/enums',
        ],
    ],
    'events' => [
        'paths' => [
            'php' => app_path(),
            'generated' => 'js/events',
        ],
    ],
];

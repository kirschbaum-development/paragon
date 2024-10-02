<?php

use Illuminate\Support\Facades\File;
use Kirschbaum\Paragon\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
function setupStatusEnumTestCase($app): void
{

    $fixturePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Fixtures';
    File::makeDirectory(app_path('Enums'), force: true);
    File::copyDirectory($fixturePath, app_path('Enums'));

    require_once app_path('Enums' . DIRECTORY_SEPARATOR . 'Status.php');

    $app['config']->set('paragon.enums.paths.php', app_path('Enums'));
}

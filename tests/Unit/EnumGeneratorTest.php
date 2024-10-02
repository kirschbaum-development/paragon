<?php

use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;

it('generates enums', function () {
    setupStatusEnumTestCase($this->app);

    $this->artisan(GenerateEnumsCommand::class);
    expect(resource_path(config('paragon.enums.paths.generated') . DIRECTORY_SEPARATOR . 'Status.ts'))->toBeFile();
});

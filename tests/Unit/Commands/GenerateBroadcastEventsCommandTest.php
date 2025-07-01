<?php

use Kirschbaum\Paragon\Commands\GenerateBroadcastEventsCommand;

it('generates broadcast typescript events', function () {
    // Act.
    $this->artisan(GenerateBroadcastEventsCommand::class);

    $path = resource_path(config('paragon.events.paths.generated') . DIRECTORY_SEPARATOR . 'Events.ts');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain(
            'interface EventsInterface {',
            'BroadcastAsEvent: string',
            'BroadcastEvent: string',
            'NestedBroadcastEvent: string',
            'const Events: EventsInterface = {',
            'BroadcastAsEvent: ".broadcast.as"',
            'BroadcastEvent: ".App\\\\Events\\\\BroadcastEvent"',
            'Nested: {',
            'NestedBroadcastEvent: ".App\\\\Events\\\\Nested\\\\NestedBroadcastEvent"',
            'export default Events;'
        )
        ->not->toContain('NotBroadcastEvent');
});

it('generates broadcast javascript events', function () {
    // Act.
    $this->artisan(GenerateBroadcastEventsCommand::class, ['--javascript' => true]);

    $path = resource_path(config('paragon.events.paths.generated') . DIRECTORY_SEPARATOR . 'Events.js');
    $file = file_get_contents($path);

    // Assert.
    expect($path)->toBeFile()
        ->and($file)
        ->toContain(
            'export default {',
            'BroadcastAsEvent: ".broadcast.as"',
            'BroadcastEvent: ".App\\\\Events\\\\BroadcastEvent"',
            'Nested: {',
            'NestedBroadcastEvent: ".App\\\\Events\\\\Nested\\\\NestedBroadcastEvent"',
        )
        ->not->toContain('NotBroadcastEvent');
});

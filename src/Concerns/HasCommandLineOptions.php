<?php

namespace Kirschbaum\Paragon\Concerns;

use Symfony\Component\Console\Input\InputOption;

trait HasCommandLineOptions
{
    /**
     * Get the console command options.
     *
     * @return array<int, InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption(
                name: 'javascript',
                shortcut: 'j',
                mode: InputOption::VALUE_NONE,
                description: 'Output Javascript files',
            ),
        ];
    }
}

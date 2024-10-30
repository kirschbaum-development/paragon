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
            new InputOption(
                name: 'typescript',
                shortcut: 't',
                mode: InputOption::VALUE_NONE,
                description: 'Output TypeScript files',
            ),
        ];
    }

    public function generateAs(): GenerateAs
    {
        return match (true) {
            $this->option('javascript') => GenerateAs::Javascript,
            $this->option('typescript') => GenerateAs::TypeScript,
            default => GenerateAs::from(config()->string('paragon.generate-as')),
        };
    }
}

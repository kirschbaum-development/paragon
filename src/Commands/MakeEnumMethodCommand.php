<?php

namespace Kirschbaum\Paragon\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Kirschbaum\Paragon\Generators\AbstractEnumGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

use function Laravel\Prompts\text;

#[AsCommand(name: 'paragon:enum-method', description: 'Create a new typescript enum method')]
class MakeEnumMethodCommand extends GeneratorCommand
{
    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/method.stub';
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        parent::handle();

        app(AbstractEnumGenerator::class)();

        $this->components
            ->info("Abstract enum class has been rebuilt to include new [{$this->argument('name')}] method.");

        return self::SUCCESS;
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the enum method'],
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => fn () => text(
                label: 'What is the name of the new enum method?',
                placeholder: 'e.g. asOptions',
            ),
        ];
    }

    /**
     * Build the file with the given name.
     *
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return str_replace('{{ Method }}', $this->argument('name'), $stub);
    }

    /**
     * Get the destination class path.
     */
    protected function getPath($name): string
    {
        return resource_path(config('paragon.enums.paths.methods')) . "/{$this->argument('name')}.ts";
    }
}

<?php

namespace Kirschbaum\Paragon\Commands;

use Exception;
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
     * Execute the console command.
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function handle(): ?bool
    {
        parent::handle();

        app(AbstractEnumGenerator::class)();

        $this->components->info("Abstract enum class has been rebuilt to include new [{$this->name()}] method.");

        return true;
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/method.stub';
    }

    /**
     * Get the console command arguments.
     *
     * @return array<int, array{string, int, string}>
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the enum method'],
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
     *
     * @return array<string, callable>
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
     * @throws Exception
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return str_replace('{{ Method }}', $this->name(), $stub);
    }

    /**
     * Get the destination class path.
     *
     * @throws Exception
     */
    protected function getPath($name): string
    {
        return resource_path(config('paragon.enums.paths.methods')) . "/{$this->name()}.ts";
    }

    /**
     * Get the method name.
     *
     * @throws Exception
     */
    protected function name(): string
    {
        $name = $this->argument('name');

        if (is_string($name)) {
            return $name;
        }

        throw new Exception('[name] argument is not a string.');
    }
}

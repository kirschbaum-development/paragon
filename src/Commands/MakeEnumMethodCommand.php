<?php

namespace Kirschbaum\Paragon\Commands;

use Exception;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Kirschbaum\Paragon\Concerns\Builders\EnumBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumJsBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumTsBuilder;
use Kirschbaum\Paragon\Generators\AbstractEnumGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\text;

#[AsCommand(name: 'paragon:enum:add-method', description: 'Create a new global typescript method to be applied to every generated enum')]
class MakeEnumMethodCommand extends GeneratorCommand
{
    /**
     * Execute the console command.
     *
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function handle(): ?bool
    {
        parent::handle();

        app(AbstractEnumGenerator::class, ['builder' => $this->builder()])();

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
     * @param  string  $name
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
        /** @var string */
        $methods = config('paragon.enums.paths.methods');
        $extension = $this->option('javascript') ? 'js' : 'ts';

        return resource_path($methods) . "/{$this->name()}.{$extension}";
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

    protected function builder(): EnumBuilder
    {
        return $this->option('javascript')
            ? app(EnumJsBuilder::class)
            : app(EnumTsBuilder::class);

    }

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

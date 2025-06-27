<?php

namespace Kirschbaum\Paragon\Commands;

use Exception;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Kirschbaum\Paragon\Concerns\Builders\EnumBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumJsBuilder;
use Kirschbaum\Paragon\Concerns\Builders\EnumTsBuilder;
use Kirschbaum\Paragon\Concerns\DiscoverEnums;
use Kirschbaum\Paragon\Generators\AbstractEnumGenerator;
use Kirschbaum\Paragon\Generators\EnumGenerator;
use ReflectionEnum;
use ReflectionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use UnitEnum;

use function Laravel\Prompts\search;
use function Laravel\Prompts\text;

#[AsCommand(name: 'paragon:enum:add-method', description: 'Create a new global typescript method to be applied to every generated enum')]
class MakeEnumMethodCommand extends GeneratorCommand
{
    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     * @throws Throwable
     */
    public function handle(): ?bool
    {
        parent::handle();

        $this->runGenerator();

        $this->writeInfo();

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
     * Interact further with the user if they were prompted for missing arguments.
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        if (
            is_string($this->option('enum'))
            || $this->option('global')
        ) {
            return;
        }

        /**
         * @var string $enumPath
         */
        $enumPath = config('paragon.enums.paths.php');

        $enums = DiscoverEnums::within(app_path($enumPath))
            ->mapWithKeys(fn (ReflectionEnum $reflector) => [$reflector->getName() => $reflector->getName()]);

        $enum = search(
            label: 'Which enum should this method be created for?',
            options: fn (string $value) => strlen($value) > 0
                ? $enums->filter(fn (string $enum): bool => Str::contains($enum, $value, ignoreCase: true))->all()
                : []
        );

        $input->setOption('enum', $enum);
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
     * @throws Throwable
     */
    protected function getPath($name): string
    {
        /**
         * @var string $path
         */
        $path = config('paragon.enums.paths.methods');
        $extension = $this->option('javascript') ? 'js' : 'ts';

        if (! $this->option('global')) {
            $enum = str($this->enumOption())->replace('/', '\\');

            throw_unless(
                enum_exists($enum->toString()),
                ReflectionException::class,
                "Class \"{$enum}\" does not exist"
            );

            $path = $enum->replace('\\', '/')
                ->prepend(Str::finish($path, '/'));
        }

        return resource_path($path) . "/{$this->name()}.{$extension}";
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
     * @throws ReflectionException
     * @throws Throwable
     */
    protected function runGenerator(): void
    {
        $this->option('global')
            ? app(AbstractEnumGenerator::class, ['builder' => $this->builder()])()
            : app(EnumGenerator::class, [
                'enum' => new ReflectionEnum($this->enumOption()),
                'builder' => $this->builder(),
                'forceRegenerate' => true,
            ])();
    }

    protected function writeInfo(): void
    {
        $name = $this->option('global')
            ? 'Abstract'
            : Str::afterLast($this->enumOption(), '\\');

        $this->components->info("[{$name}] enum class has been rebuilt to include new [{$this->name()}()] method.");
    }

    /**
     * @return class-string<UnitEnum>
     *
     * @throws Throwable
     */
    protected function enumOption(): string
    {
        $enum = $this->option('enum');

        throw_unless(
            is_string($enum) && is_a($enum, UnitEnum::class, true),
            InvalidArgumentException::class,
            'The enum option must be a valid class-string of a UnitEnum'
        );

        return $enum;
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
                name: 'enum',
                shortcut: 'e',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Fully qualified namespace of enum to use',
            ),
            new InputOption(
                name: 'global',
                shortcut: 'g',
                mode: InputOption::VALUE_NONE,
                description: 'Create global enum method',
            ),
            new InputOption(
                name: 'javascript',
                shortcut: 'j',
                mode: InputOption::VALUE_NONE,
                description: 'Output Javascript files',
            ),
        ];
    }
}

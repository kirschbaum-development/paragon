<?php

namespace Kirschbaum\Paragon\Generators;

use BackedEnum;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Kirschbaum\Paragon\Concerns\Builders\EnumBuilder;
use Kirschbaum\Paragon\Concerns\IgnoreParagon;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use ReflectionMethod;

class EnumGenerator
{
    protected Filesystem $cache;

    protected Filesystem $files;

    protected ReflectionEnum $reflector;

    /**
     * Create new EnumGenerator instance.
     *
     * @param  class-string<\UnitEnum>  $enum
     */
    public function __construct(protected string $enum, protected EnumBuilder $builder)
    {
        $this->files = Storage::createLocalDriver([
            'root' => resource_path(config()->string('paragon.enums.paths.generated')),
        ]);

        $this->cache = Storage::createLocalDriver([
            'root' => storage_path('framework/cache/paragon'),
        ]);

        if (! enum_exists($this->enum)) {
            return;
        }

        $this->reflector = new ReflectionEnum($this->enum);
    }

    public function __invoke(): bool
    {
        if ($this->generatedFileExists() && $this->cached()) {
            return false;
        }

        $this->files->put($this->path(), $this->contents());

        $this->cacheEnum();

        return true;
    }

    /**
     * Typescript enum file contents.
     */
    protected function contents(): string
    {
        $code = $this->prepareEnumCode();

        return str(file_get_contents($this->builder->stubPath()) ?: null)
            ->replace('{{ Path }}', $this->relativePath())
            ->replace('{{ Enum }}', class_basename($this->enum))
            ->replace('{{ Abstract }}', config()->string('paragon.enums.abstract-class'))
            ->replace('{{ TypeDefinition }}', $code->get('type') ?? '')
            ->replace('{{ Cases }}', $code->get('cases') ?? '')
            ->replace('{{ Getters }}', $code->get('getters') ?? '');
    }

    /**
     * Prepare all the data needed for each enum case object.
     *
     * @return Fluent<string, string>
     */
    protected function prepareEnumCode(): Fluent
    {
        $cases = collect($this->reflector->getCases());

        return new Fluent([
            'type' => $this->buildTypeDefinition(),
            'cases' => $this->buildCases($cases),
            'getters' => $this->buildGetters($cases),
        ]);
    }

    /**
     * Relative path to the abstract enum class.
     */
    protected function relativePath(): string
    {
        $depth = str($this->enum)->after('App\\Enums\\')->explode('\\')->count() - 1;

        return $depth
            ? collect(range(1, $depth))->map(fn () => '../')->join('')
            : './';
    }

    /**
     * Prepare the definition for each case's return type.
     */
    protected function buildTypeDefinition(): string
    {
        return $this->methods()
            ->map(fn ($method) => PHP_EOL . "    {$method->getName()}();")
            ->sortDesc()
            ->when(
                $this->reflector->isBacked(),
                fn ($collection) => $collection->push(PHP_EOL . "    value: {$this->valueReturnType()};")
            )
            ->reverse()
            ->join('');
    }

    /**
     * Determine the public methods available for the enum.
     *
     * @return Collection<int, ReflectionMethod>
     */
    protected function methods(): Collection
    {
        return collect($this->reflector->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(function (ReflectionMethod $method) {
                return $method->isStatic() || $method->getAttributes(IgnoreParagon::class);
            })
            ->sortBy(fn (ReflectionMethod $method) => $method->getName());
    }

    /**
     * Determine the value return type for the type definition.
     */
    protected function valueReturnType(): string
    {
        return $this->reflector->getBackingType()?->getName() === 'int' ? 'number' : 'string';
    }

    /**
     * Build all the case objects.
     *
     * @param  Collection<int,ReflectionEnumUnitCase|ReflectionEnumBackedCase>  $cases
     */
    protected function buildCases(Collection $cases): string
    {
        return $cases
            ->map(function (ReflectionEnumUnitCase $case) {
                $value = $this->caseValueProperty($case);

                $methodValues = $this->methods()
                    ->map(fn (ReflectionMethod $method): string => $this->builder->caseMethod($method, $case));

                return $this->assembleCaseObject($case, $value, $methodValues);
            })
            ->join(',' . PHP_EOL);
    }

    /**
     * Prepare the value of the enum case object if it is a backed enum.
     */
    protected function caseValueProperty(ReflectionEnumUnitCase|ReflectionEnumBackedCase $case): string
    {
        if ($this->reflector->isBacked()) {
            return str('value: ')
                ->prepend(PHP_EOL . '            ')
                ->when(
                    $this->reflector->getBackingType()->getName() === 'int',
                    fn ($string) => $case->getValue() instanceof BackedEnum
                        ? $string->append("{$case->getValue()->value}")
                        : $string,
                    fn ($string) => $case->getValue() instanceof BackedEnum
                        ? $string->append("'{$case->getValue()->value}'")
                        : $string,
                )
                ->append(',');
        }

        return '';
    }

    /**
     * Assemble the actual enum case object code including the name, value if needed, and any public methods.
     *
     * @param  Collection<int,string>  $methodValues
     */
    protected function assembleCaseObject(
        ReflectionEnumUnitCase|ReflectionEnumBackedCase $case,
        string $value,
        Collection $methodValues,
    ): string {
        $name = str('name: ')->append("'{$case->name}'")->append(',');

        return <<<JS
                {$case->name}: Object.freeze({
                    {$name}{$value}{$methodValues->join('')}
                })
        JS;
    }

    /**
     * Build all case object getter methods.
     *
     * @param  Collection<int,ReflectionEnumUnitCase|ReflectionEnumBackedCase>  $cases
     */
    protected function buildGetters(Collection $cases): string
    {
        return $cases
            ->map(fn ($case) => $this->builder->assembleCaseGetter($case))
            ->join(PHP_EOL . PHP_EOL);
    }

    /**
     * Path where the enum will be saved.
     */
    protected function path(): string
    {
        return str($this->enum)
            ->after('App\\Enums\\')
            ->replace('\\', '/')
            ->finish($this->builder->fileExtension());
    }

    protected function generatedFileExists(): bool
    {
        return $this->files->exists($this->path());
    }

    /*
    |--------------------------------------------------------------------------
    | Enum Caching
    |--------------------------------------------------------------------------
    */

    protected function cached(): bool
    {
        return $this->cache->get($this->hashFilename()) === $this->hashFile();
    }

    protected function hashFilename(): string
    {
        return md5((string) $this->reflector->getFileName());
    }

    protected function hashFile(): string
    {
        return (string) md5_file((string) $this->reflector->getFileName());
    }

    protected function cacheEnum(): void
    {
        $this->cache->put($this->hashFilename(), $this->hashFile());
    }
}

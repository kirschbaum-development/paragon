<?php

namespace Kirschbaum\Paragon\Generators;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Paragon\Concerns\Builders\EnumBuilder;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem as FileUtility;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class AbstractEnumGenerator
{
    protected Filesystem $files;

    public function __construct(protected EnumBuilder $builder)
    {
        /** @var string */
        $generatedPath = config('paragon.enums.paths.generated');

        $this->files = Storage::createLocalDriver([
            'root' => resource_path($generatedPath),
        ]);
    }

    public function __invoke(): void
    {
        $this->files->put($this->path(), $this->contents());
    }

    /**
     * Inject all prepared data into the stub.
     */
    protected function contents(): string
    {
        $imports = $this->imports();
        $suffix = $imports->count() ? PHP_EOL : '';
        /** @var string */
        $abstractClass = config('paragon.enums.abstract-class');

        return str((string) file_get_contents($this->builder->abstractStubPath()))
            ->replace('{{ Abstract }}', $abstractClass)
            ->replace('{{ Imports }}', "{$imports->join('')}{$suffix}")
            ->replace('{{ Methods }}', "{$this->methods($imports->keys())}{$suffix}");
    }

    /**
     * Build out the actual enum case object including the name, value if needed, and any public methods.
     *
     * @return Collection<string, string>
     */
    protected function imports(): Collection
    {
        /** @var string */
        $methodsPath = config('paragon.enums.paths.methods');

        try {
            $files = Finder::create()
                ->files()
                ->in(resource_path($methodsPath));
        } catch (DirectoryNotFoundException) {
            return collect();
        }

        /**
         * @var Collection<int, SplFileInfo> $fileCollection
         */
        $fileCollection = collect($files);

        return $fileCollection
            ->mapWithKeys(function (SplFileInfo $file): array {
                $filesystem = new FileUtility();
                /** @var string */
                $generatedPath = config('paragon.enums.paths.generated');

                $relativeFilePath = $filesystem->makePathRelative(
                    $file->getPath(),
                    resource_path($generatedPath)
                );

                $name = (string) str($file->getFileName())->before('.');

                /** @var array<string,string> */
                return [$name => "import {$name} from '{$relativeFilePath}{$file->getFilename()}';" . PHP_EOL];
            })
            ->sort();
    }

    /**
     * Build out the actual enum case object including the name, value if needed, and any public methods.
     *
     * @param  Collection<int, string>  $methods
     */
    protected function methods(Collection $methods): string
    {
        return $methods
            ->map(fn (string $method): string => PHP_EOL . "Enum.{$method} = {$method};")
            ->join('');
    }

    /**
     * Path where the enum will be saved.
     */
    protected function path(): string
    {
        return config('paragon.enums.abstract-class') . $this->builder->fileExtension();
    }
}

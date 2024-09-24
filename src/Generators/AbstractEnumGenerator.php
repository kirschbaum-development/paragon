<?php

namespace Kirschbaum\Paragon\Generators;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class AbstractEnumGenerator
{
    protected Filesystem $files;

    public function __construct()
    {
        $this->files = Storage::createLocalDriver([
            'root' => resource_path(config('paragon.enums.paths.generated')),
        ]);
    }

    public function __invoke(): void
    {
        $this->files->put($this->path(), $this->contents());
    }

    public static function generate()
    {
        return (new self())();
    }

    /**
     * Inject all prepared data into the stub.
     */
    protected function contents(): string
    {
        $imports = $this->imports();
        $suffix = $imports->count() ? PHP_EOL : '';

        return str(file_get_contents($this->stubPath()))
            ->replace('{{ Abstract }}', config('paragon.enums.abstract-class'))
            ->replace('{{ Imports }}', "{$imports->join('')}{$suffix}")
            ->replace('{{ Methods }}', "{$this->methods($imports->keys())}{$suffix}");
    }

    /**
     * Get the path to the stubs.
     */
    public function stubPath(): string
    {
        return __DIR__ . '/../../stubs/abstract-enum.stub';
    }

    /**
     * Build out the actual enum case object including the name, value if needed, and any public methods.
     */
    protected function imports(): Collection
    {
        try {
            $files = Finder::create()
                ->files()
                ->in(resource_path(config('paragon.enums.paths.methods')));
        } catch (DirectoryNotFoundException) {
            return collect();
        }

        return collect($files)
            ->mapWithKeys(function ($file) {
                $abstractPath = collect(explode('/', config('paragon.enums.paths.generated')));
                $relativeFilePath = str($file->getPath())
                    ->after(resource_path())
                    ->ltrim('/')
                    ->explode('/')
                    ->map(fn ($directory, $index) => data_get($abstractPath, $index) === $directory ? '..' : $directory)
                    ->filter()
                    ->join('/');

                $name = (string) str($file->getFileName())->before('.');

                return [$name => "import {$name} from '{$relativeFilePath}/{$file->getFilename()}';" . PHP_EOL];
            })
            ->sort();
    }

    /**
     * Build out the actual enum case object including the name, value if needed, and any public methods.
     */
    protected function methods(Collection $methods): string
    {
        return $methods->map(fn ($method) => PHP_EOL . "Enum.{$method} = {$method};")
            ->join('');
    }

    /**
     * Path where the enum will be saved.
     */
    protected function path(): string
    {
        return config('paragon.enums.abstract-class') . '.ts';
    }
}

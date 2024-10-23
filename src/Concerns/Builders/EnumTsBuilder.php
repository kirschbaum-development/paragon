<?php

namespace Kirschbaum\Paragon\Concerns\Builders;

use BackedEnum;
use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use ReflectionMethod;

class EnumTsBuilder implements EnumBuilder
{
    /**
     * Get the path to the stub.
     */
    public function stubPath(): string
    {
        return __DIR__ . '/../../../stubs/enum.stub';
    }

    /**
     * Get the path to the stub.
     */
    public function abstractStubPath(): string
    {
        return __DIR__ . '/../../../stubs/abstract-enum.stub';
    }

    /**
     * File extension.
     */
    public function fileExtension(): string
    {
        return '.ts';
    }

    /**
     * Prepare the method and its respective values so it can get injected into the case object.
     */
    public function caseMethod(ReflectionMethod $method, ReflectionEnumUnitCase|ReflectionEnumBackedCase $case): string
    {
        $value = $case->getValue()->{$method->getName()}();
        $class = class_basename($method->getDeclaringClass()->getName());

        return str(PHP_EOL . "            {$method->getName()}: (): ")
            ->append(match (true) {
                $value instanceof BackedEnum => "object => {$class}.{$value->name}",
                is_numeric($value) => "number => {$value}",
                is_null($value) => 'null => null',
                default => "string => '{$value}'"
            })
            ->append(',');
    }

    /**
     * Assemble the static getter method code for the enum case object.
     */
    public function assembleCaseGetter(ReflectionEnumUnitCase|ReflectionEnumBackedCase $case): string
    {
        $class = class_basename($case->getDeclaringClass()->name);

        return <<<JS
            public static get {$case->name}(): {$class}Definition {
                return this.items['{$case->name}'];
            }
        JS;
    }
}

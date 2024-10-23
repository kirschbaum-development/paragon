<?php

namespace Kirschbaum\Paragon\Concerns\Builders;

use BackedEnum;
use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use ReflectionMethod;

class EnumJsBuilder implements EnumBuilder
{
    /**
     * Get the path to the stub.
     */
    public function stubPath(): string
    {
        return __DIR__ . '/../../../stubs/enum-js.stub';
    }

    /**
     * Get the path to the abstract stub.
     */
    public function abstractStubPath(): string
    {
        return __DIR__ . '/../../../stubs/abstract-enum-js.stub';
    }

    /**
     * File extension.
     */
    public function fileExtension(): string
    {
        return '.js';
    }

    /**
     * Prepare the method and its respective values so it can get injected into the case object.
     */
    public function caseMethod(ReflectionMethod $method, ReflectionEnumUnitCase|ReflectionEnumBackedCase $case): string
    {
        $value = $case->getValue()->{$method->getName()}();
        $class = class_basename($method->getDeclaringClass()->getName());

        return str(PHP_EOL . "            {$method->getName()}: () ")
            ->append(match (true) {
                $value instanceof BackedEnum => "=> {$class}.{$value->name}",
                is_numeric($value) => "=> {$value}",
                is_null($value) => '=> null',
                default => "=> '{$value}'"
            })
            ->append(',');
    }

    /**
     * Assemble the static getter method code for the enum case object.
     */
    public function assembleCaseGetter(ReflectionEnumUnitCase|ReflectionEnumBackedCase $case): string
    {
        return <<<JS
            static get {$case->name}() {
                return this.items['{$case->name}'];
            }
        JS;
    }
}

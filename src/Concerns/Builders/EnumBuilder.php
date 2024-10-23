<?php

namespace Kirschbaum\Paragon\Concerns\Builders;

use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use ReflectionMethod;

interface EnumBuilder
{
    /**
     * Get the path to the stub.
     */
    public function stubPath(): string;

    /**
     * Get the path to the abstract stub.
     */
    public function abstractStubPath(): string;

    /**
     * File extension.
     */
    public function fileExtension(): string;

    /**
     * Prepare the method and its respective values so it can get injected into the case object.
     */
    public function caseMethod(ReflectionMethod $method, ReflectionEnumUnitCase|ReflectionEnumBackedCase $case): string;

    /**
     * Assemble the static getter method code for the enum case object.
     */
    public function assembleCaseGetter(ReflectionEnumUnitCase|ReflectionEnumBackedCase $case): string;
}

<?php

declare(strict_types=1);

namespace Rector\Privatization\Reflection;

use ReflectionClass;

final class ClassConstantsResolver
{
    /**
     * @var array<string, array<string, string>>
     */
    private $cachedConstantNamesToValues = [];

    /**
     * @return array<string, string>
     */
    public function getClassConstantNamesToValues(string $classWithConstants): array
    {
        if (isset($this->cachedConstantNamesToValues[$classWithConstants])) {
            return $this->cachedConstantNamesToValues[$classWithConstants];
        }

        $reflectionClass = new ReflectionClass($classWithConstants);
        $constantNamesToValues = $reflectionClass->getConstants();

        $this->cachedConstantNamesToValues = $constantNamesToValues;

        return $constantNamesToValues;
    }
}

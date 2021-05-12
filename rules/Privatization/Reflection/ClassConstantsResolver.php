<?php

declare(strict_types=1);

namespace Rector\Privatization\Reflection;

use PHPStan\Reflection\ReflectionProvider;

final class ClassConstantsResolver
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $cachedConstantNamesToValues = [];

    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getClassConstantNamesToValues(string $classWithConstants): array
    {
        if (isset($this->cachedConstantNamesToValues[$classWithConstants])) {
            return $this->cachedConstantNamesToValues[$classWithConstants];
        }

        if (! $this->reflectionProvider->hasClass($classWithConstants)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($classWithConstants);
        $reflectionClass = $classReflection->getNativeReflection();

        $constantNamesToValues = $reflectionClass->getConstants();
        $this->cachedConstantNamesToValues = $constantNamesToValues;

        return $constantNamesToValues;
    }
}

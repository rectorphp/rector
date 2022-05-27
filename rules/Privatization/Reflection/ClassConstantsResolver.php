<?php

declare (strict_types=1);
namespace Rector\Privatization\Reflection;

use PHPStan\Reflection\ReflectionProvider;
final class ClassConstantsResolver
{
    /**
     * @var array<string, array<string, string>>
     */
    private $cachedConstantNamesToValues = [];
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return array<string, mixed>
     */
    public function getClassConstantNamesToValues(string $classWithConstants) : array
    {
        if (isset($this->cachedConstantNamesToValues[$classWithConstants])) {
            return $this->cachedConstantNamesToValues[$classWithConstants];
        }
        if (!$this->reflectionProvider->hasClass($classWithConstants)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($classWithConstants);
        $nativeReflection = $classReflection->getNativeReflection();
        $constantNamesToValues = $nativeReflection->getConstants();
        $this->cachedConstantNamesToValues = $constantNamesToValues;
        return $constantNamesToValues;
    }
}

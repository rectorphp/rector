<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeAnalyzer;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
final class NamedArgumentsResolver
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param class-string $class
     * @return array<int, string>
     */
    public function resolveFromClass(string $class) : array
    {
        // decorate args with names if attribute class was found
        if (!$this->reflectionProvider->hasClass($class)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($class);
        if (!$classReflection->hasConstructor()) {
            return [];
        }
        $argumentNames = [];
        $constructorMethodReflection = $classReflection->getConstructor();
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($constructorMethodReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $key => $parameterReflection) {
            /** @var ParameterReflection $parameterReflection */
            $argumentNames[$key] = $parameterReflection->getName();
        }
        return $argumentNames;
    }
}

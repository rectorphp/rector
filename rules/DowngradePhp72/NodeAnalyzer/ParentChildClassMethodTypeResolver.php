<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;

final class ParentChildClassMethodTypeResolver
{
    /**
     * @var NativeTypeClassTreeResolver
     */
    private $nativeTypeClassTreeResolver;

    public function __construct(NativeTypeClassTreeResolver $nativeTypeClassTreeResolver)
    {
        $this->nativeTypeClassTreeResolver = $nativeTypeClassTreeResolver;
    }

    /**
     * @return array<class-string, Type>
     */
    public function resolve(
        ClassReflection $classReflection,
        string $methodName,
        int $position,
        Scope $scope
    ): array {
        $parameterTypesByClassName = [];

        // include types of class scope in case of trait
        if ($classReflection->isTrait()) {
            $parameterTypesByInterfaceName = $this->resolveInterfaceTypeByClassName($scope, $methodName, $position);
            $parameterTypesByClassName = array_merge($parameterTypesByClassName, $parameterTypesByInterfaceName);
        }

        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if (! $ancestorClassReflection->hasMethod($methodName)) {
                continue;
            }

            $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType(
                $ancestorClassReflection,
                $methodName,
                $position
            );

            $parameterTypesByClassName[$ancestorClassReflection->getName()] = $parameterType;
        }

        return $parameterTypesByClassName;
    }

    /**
     * @return array<class-string, Type>
     */
    private function resolveInterfaceTypeByClassName(Scope $scope, string $methodName, int $position): array
    {
        $typesByClassName = [];

        $currentClassReflection = $scope->getClassReflection();
        if (! $currentClassReflection instanceof ClassReflection) {
            return [];
        }

        foreach ($currentClassReflection->getInterfaces() as $interfaceClassReflection) {
            if (! $interfaceClassReflection->hasMethod($methodName)) {
                continue;
            }

            $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType(
                $interfaceClassReflection,
                $methodName,
                $position
            );

            $typesByClassName[$interfaceClassReflection->getName()] = $parameterType;
        }

        return $typesByClassName;
    }
}

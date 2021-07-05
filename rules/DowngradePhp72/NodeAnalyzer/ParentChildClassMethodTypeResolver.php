<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

final class ParentChildClassMethodTypeResolver
{
    public function __construct(
        private NativeTypeClassTreeResolver $nativeTypeClassTreeResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    /**
     * @param array<class-string, array<string, ClassMethod[]>> $classMethodStack
     * @return array<class-string, Type>
     */
    public function resolve(
        ClassReflection $classReflection,
        string $methodName,
        int $paramPosition,
        array $classMethodStack
    ): array {
        $parameterTypesByClassName = [];

        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if (! $ancestorClassReflection->hasMethod($methodName)) {
                continue;
            }

            $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType(
                $ancestorClassReflection,
                $methodName,
                $paramPosition
            );

            if (! $parameterType instanceof Type) {
                continue;
            }

            $parameterTypesByClassName[$ancestorClassReflection->getName()] = $parameterType;
        }

        $stackedParameterTypesByClassName = $this->resolveStackedClassMethodTypes(
            $classReflection,
            $classMethodStack,
            $methodName,
            $paramPosition
        );

        return array_merge($parameterTypesByClassName, $stackedParameterTypesByClassName);
    }

    /**
     * @param array<class-string, array<string, ClassMethod[]>> $classMethodStack
     * @return array<class-string, Type>
     */
    private function resolveStackedClassMethodTypes(
        ClassReflection $classReflection,
        array $classMethodStack,
        string $methodName,
        int $paramPosition
    ): array {
        $stackedParameterTypesByClassName = [];

        // get subclasses of ancestors too
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            foreach ($classMethodStack as $className => $methodNameToClassMethods) {
                if (! $this->reflectionProvider->hasClass($className)) {
                    continue;
                }

                // also match method name!
                if (! isset($methodNameToClassMethods[$methodName])) {
                    continue;
                }

                $stackedClassReflection = $this->reflectionProvider->getClass($className);
                if (! $stackedClassReflection->isSubclassOf($ancestorClassReflection->getName())) {
                    continue;
                }

                $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType(
                    $stackedClassReflection,
                    $methodName,
                    $paramPosition
                );

                if (! $parameterType instanceof Type) {
                    continue;
                }

                $stackedParameterTypesByClassName[$className] = $parameterType;
            }
        }

        return $stackedParameterTypesByClassName;
    }
}

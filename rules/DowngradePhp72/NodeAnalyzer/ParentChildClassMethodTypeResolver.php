<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;

final class ParentChildClassMethodTypeResolver
{
    public function __construct(
        private NativeTypeClassTreeResolver $nativeTypeClassTreeResolver,
        private NodeRepository $nodeRepository,
        private ReflectionProvider $reflectionProvider,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @param ClassReflection[] $ancestorClassReflections
     * @param ClassReflection[] $interfaceClassReflections
     *@return array<class-string, Type>
     */
    public function resolve(
        ClassReflection $classReflection,
        string $methodName,
        int $paramPosition,
        array $ancestorClassReflections,
        array $interfaceClassReflections
    ): array {
        $parameterTypesByClassName = [];

        // include types of class scope in case of trait
        if ($classReflection->isTrait()) {
            $parameterTypesByInterfaceName = $this->resolveInterfaceTypeByClassName(
                $interfaceClassReflections,
                $methodName,
                $paramPosition
            );
            $parameterTypesByClassName = array_merge($parameterTypesByClassName, $parameterTypesByInterfaceName);
        }

        foreach ($ancestorClassReflections as $ancestorClassReflection) {
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

            // collect other children
            if ($ancestorClassReflection->isInterface() || $ancestorClassReflection->isClass()) {
                $interfaceParameterTypesByClassName = $this->collectInterfaceImplenters(
                    $ancestorClassReflection,
                    $methodName,
                    $paramPosition
                );

                $parameterTypesByClassName = array_merge(
                    $parameterTypesByClassName,
                    $interfaceParameterTypesByClassName
                );
            }
        }

        return $parameterTypesByClassName;
    }

    /**
     * @param ClassReflection[] $interfaces
     * @return array<class-string, Type>
     */
    private function resolveInterfaceTypeByClassName(array $interfaces, string $methodName, int $position): array
    {
        $typesByClassName = [];

        foreach ($interfaces as $interface) {
            $interfaceHasMethod = $interface->hasMethod($methodName);
            if (! $interfaceHasMethod) {
                continue;
            }

            $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType(
                $interface,
                $methodName,
                $position
            );

            // parameter does not exist
            if (! $parameterType instanceof Type) {
                continue;
            }

            $typesByClassName[$interface->getName()] = $parameterType;
        }

        return $typesByClassName;
    }

    /**
     * @return array<class-string, Type>
     */
    private function collectInterfaceImplenters(
        ClassReflection $ancestorClassReflection,
        string $methodName,
        int $paramPosition
    ): array {
        $parameterTypesByClassName = [];

        $interfaceImplementerClassLikes = $this->nodeRepository->findClassesAndInterfacesByType(
            $ancestorClassReflection->getName()
        );

        foreach ($interfaceImplementerClassLikes as $interfaceImplementerClassLike) {
            $interfaceImplementerClassLikeName = $this->nodeNameResolver->getName($interfaceImplementerClassLike);
            if ($interfaceImplementerClassLikeName === null) {
                continue;
            }

            /** @var class-string $interfaceImplementerClassLikeName */
            if (! $this->reflectionProvider->hasClass($interfaceImplementerClassLikeName)) {
                continue;
            }

            $interfaceImplementerClassReflection = $this->reflectionProvider->getClass(
                $interfaceImplementerClassLikeName
            );
            $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType(
                $interfaceImplementerClassReflection,
                $methodName,
                $paramPosition
            );

            if (! $parameterType instanceof Type) {
                continue;
            }

            $parameterTypesByClassName[$interfaceImplementerClassLikeName] = $parameterType;
        }

        return $parameterTypesByClassName;
    }
}

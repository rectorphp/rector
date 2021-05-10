<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
final class ParentChildClassMethodTypeResolver
{
    /**
     * @var \Rector\DowngradePhp72\NodeAnalyzer\NativeTypeClassTreeResolver
     */
    private $nativeTypeClassTreeResolver;
    /**
     * @var \Rector\NodeCollector\NodeCollector\NodeRepository
     */
    private $nodeRepository;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\DowngradePhp72\NodeAnalyzer\NativeTypeClassTreeResolver $nativeTypeClassTreeResolver, \Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nativeTypeClassTreeResolver = $nativeTypeClassTreeResolver;
        $this->nodeRepository = $nodeRepository;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return array<class-string, Type>
     */
    public function resolve(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName, int $paramPosition, \PHPStan\Analyser\Scope $scope) : array
    {
        $parameterTypesByClassName = [];
        // include types of class scope in case of trait
        if ($classReflection->isTrait()) {
            $parameterTypesByInterfaceName = $this->resolveInterfaceTypeByClassName($scope, $methodName, $paramPosition);
            $parameterTypesByClassName = \array_merge($parameterTypesByClassName, $parameterTypesByInterfaceName);
        }
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if (!$ancestorClassReflection->hasMethod($methodName)) {
                continue;
            }
            $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType($ancestorClassReflection, $methodName, $paramPosition);
            $parameterTypesByClassName[$ancestorClassReflection->getName()] = $parameterType;
            // collect other children
            if ($ancestorClassReflection->isInterface() || $ancestorClassReflection->isClass()) {
                $interfaceParameterTypesByClassName = $this->collectInterfaceImplenters($ancestorClassReflection, $methodName, $paramPosition);
                $parameterTypesByClassName = \array_merge($parameterTypesByClassName, $interfaceParameterTypesByClassName);
            }
        }
        return $parameterTypesByClassName;
    }
    /**
     * @return array<class-string, Type>
     */
    private function resolveInterfaceTypeByClassName(\PHPStan\Analyser\Scope $scope, string $methodName, int $position) : array
    {
        $typesByClassName = [];
        $currentClassReflection = $scope->getClassReflection();
        if (!$currentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return [];
        }
        foreach ($currentClassReflection->getInterfaces() as $interfaceClassReflection) {
            if (!$interfaceClassReflection->hasMethod($methodName)) {
                continue;
            }
            $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType($interfaceClassReflection, $methodName, $position);
            $typesByClassName[$interfaceClassReflection->getName()] = $parameterType;
        }
        return $typesByClassName;
    }
    /**
     * @return array<class-string, Type>
     */
    private function collectInterfaceImplenters(\PHPStan\Reflection\ClassReflection $ancestorClassReflection, string $methodName, int $paramPosition) : array
    {
        $parameterTypesByClassName = [];
        $interfaceImplementerClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($ancestorClassReflection->getName());
        foreach ($interfaceImplementerClassLikes as $interfaceImplementerClassLike) {
            $interfaceImplementerClassLikeName = $this->nodeNameResolver->getName($interfaceImplementerClassLike);
            if ($interfaceImplementerClassLikeName === null) {
                continue;
            }
            /** @var class-string $interfaceImplementerClassLikeName */
            if (!$this->reflectionProvider->hasClass($interfaceImplementerClassLikeName)) {
                continue;
            }
            $interfaceImplementerClassReflection = $this->reflectionProvider->getClass($interfaceImplementerClassLikeName);
            $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType($interfaceImplementerClassReflection, $methodName, $paramPosition);
            $parameterTypesByClassName[$interfaceImplementerClassLikeName] = $parameterType;
        }
        return $parameterTypesByClassName;
    }
}

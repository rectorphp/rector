<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
final class ParentChildClassMethodTypeResolver
{
    /**
     * @var \Rector\DowngradePhp72\NodeAnalyzer\NativeTypeClassTreeResolver
     */
    private $nativeTypeClassTreeResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\DowngradePhp72\NodeAnalyzer\NativeTypeClassTreeResolver $nativeTypeClassTreeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nativeTypeClassTreeResolver = $nativeTypeClassTreeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param array<class-string, array<string, ClassMethod[]>> $classMethodStack
     * @return array<class-string, Type>
     */
    public function resolve(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName, int $paramPosition, array $classMethodStack) : array
    {
        $parameterTypesByClassName = [];
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if (!$ancestorClassReflection->hasMethod($methodName)) {
                continue;
            }
            $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType($ancestorClassReflection, $methodName, $paramPosition);
            if (!$parameterType instanceof \PHPStan\Type\Type) {
                continue;
            }
            $parameterTypesByClassName[$ancestorClassReflection->getName()] = $parameterType;
        }
        $stackedParameterTypesByClassName = $this->resolveStackedClassMethodTypes($classReflection, $classMethodStack, $methodName, $paramPosition);
        return \array_merge($parameterTypesByClassName, $stackedParameterTypesByClassName);
    }
    /**
     * @param array<class-string, array<string, ClassMethod[]>> $classMethodStack
     * @return array<class-string, Type>
     */
    private function resolveStackedClassMethodTypes(\PHPStan\Reflection\ClassReflection $classReflection, array $classMethodStack, string $methodName, int $paramPosition) : array
    {
        $stackedParameterTypesByClassName = [];
        // get subclasses of ancestors too
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            foreach (\array_keys($classMethodStack) as $className) {
                if (!$this->reflectionProvider->hasClass($className)) {
                    continue;
                }
                $stackedClassReflection = $this->reflectionProvider->getClass($className);
                if (!$stackedClassReflection->isSubclassOf($ancestorClassReflection->getName())) {
                    continue;
                }
                $parameterType = $this->nativeTypeClassTreeResolver->resolveParameterReflectionType($stackedClassReflection, $methodName, $paramPosition);
                if (!$parameterType instanceof \PHPStan\Type\Type) {
                    continue;
                }
                $stackedParameterTypesByClassName[$className] = $parameterType;
            }
        }
        return $stackedParameterTypesByClassName;
    }
}

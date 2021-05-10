<?php

declare (strict_types=1);
namespace Rector\Php80\Reflection;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Reflection\MethodReflectionToAstResolver;
final class MethodReflectionClassMethodResolver
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var MethodReflectionToAstResolver
     */
    private $methodReflectionToAstResolver;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\Reflection\MethodReflectionToAstResolver $methodReflectionToAstResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->methodReflectionToAstResolver = $methodReflectionToAstResolver;
    }
    public function resolve(string $className, string $methodName) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->isAnonymous()) {
            return null;
        }
        if (!$classReflection->hasMethod($methodName)) {
            return null;
        }
        $constructorClassMethodReflection = $classReflection->getNativeMethod($methodName);
        if (!$constructorClassMethodReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
            return null;
        }
        return $this->methodReflectionToAstResolver->resolveProjectClassMethod($constructorClassMethodReflection);
    }
}

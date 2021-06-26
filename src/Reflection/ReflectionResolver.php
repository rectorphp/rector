<?php

declare (strict_types=1);
namespace Rector\Core\Reflection;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ReflectionResolver
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param class-string $className
     */
    public function resolveMethodReflection(string $className, string $methodName) : ?\PHPStan\Reflection\MethodReflection
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->hasMethod($methodName)) {
            return $classReflection->getNativeMethod($methodName);
        }
        return null;
    }
    /**
     * @param class-string $className
     */
    public function resolveNativeClassMethodReflection(string $className, string $methodName) : ?\ReflectionMethod
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $reflectionClass = $classReflection->getNativeReflection();
        return $reflectionClass->hasMethod($methodName) ? $reflectionClass->getMethod($methodName) : null;
    }
    public function resolveMethodReflectionFromStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PHPStan\Reflection\MethodReflection
    {
        $objectType = $this->nodeTypeResolver->resolve($staticCall->class);
        /** @var array<class-string> $classes */
        $classes = \PHPStan\Type\TypeUtils::getDirectClassNames($objectType);
        $methodName = $this->nodeNameResolver->getName($staticCall->name);
        if ($methodName === null) {
            return null;
        }
        foreach ($classes as $class) {
            $methodReflection = $this->resolveMethodReflection($class, $methodName);
            if ($methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
                return $methodReflection;
            }
        }
        return null;
    }
    public function resolveMethodReflectionFromMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PHPStan\Reflection\MethodReflection
    {
        $callerType = $this->nodeTypeResolver->resolve($methodCall->var);
        if (!$callerType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }
        return $this->resolveMethodReflection($callerType->getClassName(), $methodName);
    }
}

<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionMethod;

final class ReflectionResolver
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private NodeTypeResolver $nodeTypeResolver,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @param class-string $className
     */
    public function resolveMethodReflection(string $className, string $methodName): ?MethodReflection
    {
        if (! $this->reflectionProvider->hasClass($className)) {
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
    public function resolveNativeClassMethodReflection(string $className, string $methodName): ?ReflectionMethod
    {
        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        $reflectionClass = $classReflection->getNativeReflection();

        return $reflectionClass->hasMethod($methodName) ? $reflectionClass->getMethod($methodName) : null;
    }

    public function resolveMethodReflectionFromStaticCall(StaticCall $staticCall): ?MethodReflection
    {
        $objectType = $this->nodeTypeResolver->resolve($staticCall->class);

        /** @var array<class-string> $classes */
        $classes = TypeUtils::getDirectClassNames($objectType);

        $methodName = $this->nodeNameResolver->getName($staticCall->name);
        if ($methodName === null) {
            return null;
        }

        foreach ($classes as $class) {
            $methodReflection = $this->resolveMethodReflection($class, $methodName);
            if ($methodReflection instanceof MethodReflection) {
                return $methodReflection;
            }
        }

        return null;
    }

    public function resolveMethodReflectionFromMethodCall(MethodCall $methodCall): ?MethodReflection
    {
        $callerType = $this->nodeTypeResolver->resolve($methodCall->var);
        if (! $callerType instanceof TypeWithClassName) {
            return null;
        }

        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }

        return $this->resolveMethodReflection($callerType->getClassName(), $methodName);
    }

    public function resolveFunctionLikeReflectionFromCall(
        MethodCall | StaticCall | FuncCall $call
    ): MethodReflection | FunctionReflection | null {
        if ($call instanceof MethodCall) {
            return $this->resolveMethodReflectionFromMethodCall($call);
        }

        if ($call instanceof StaticCall) {
            return $this->resolveMethodReflectionFromStaticCall($call);
        }

        return $this->resolveFunctionReflectionFromFuncCall($call);
    }

    public function resolveMethodReflectionFromClassMethod(ClassMethod $classMethod): ?MethodReflection
    {
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($class === null) {
            return null;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);
        return $this->resolveMethodReflection($class, $methodName);
    }

    public function resolveMethodReflectionFromNew(New_ $new): ?MethodReflection
    {
        $newClassType = $this->nodeTypeResolver->resolve($new->class);
        if (! $newClassType instanceof TypeWithClassName) {
            return null;
        }

        return $this->resolveMethodReflection($newClassType->getClassName(), MethodName::CONSTRUCT);
    }

    private function resolveFunctionReflectionFromFuncCall(FuncCall $funcCall): ?FunctionReflection
    {
        $functionName = $this->nodeNameResolver->getName($funcCall);
        if ($functionName === null) {
            return null;
        }

        $functionNameFullyQualified = new FullyQualified($functionName);
        if (! $this->reflectionProvider->hasFunction($functionNameFullyQualified, null)) {
            return null;
        }

        return $this->reflectionProvider->getFunction($functionNameFullyQualified, null);
    }
}

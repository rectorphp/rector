<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Reflection;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class ReflectionTypeResolver
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PrivatesCaller
     */
    private $privatesCaller;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        ReflectionProvider $reflectionProvider,
        NodeNameResolver $nodeNameResolver,
        PrivatesCaller $privatesCaller
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->privatesCaller = $privatesCaller;
    }

    public function resolveMethodCallReturnType(MethodCall $methodCall): ?Type
    {
        $objectType = $this->getStaticType($methodCall->var);
        if (! $objectType instanceof TypeWithClassName) {
            return null;
        }

        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }

        return $this->resolveNativeReturnTypeFromClassAndMethod($objectType->getClassName(), $methodName, $methodCall);
    }

    public function resolveStaticCallReturnType(StaticCall $staticCall): ?Type
    {
        $className = $this->nodeNameResolver->getName($staticCall->class);
        if ($className === null) {
            return null;
        }

        $methodName = $this->nodeNameResolver->getName($staticCall->name);
        if ($methodName === null) {
            return null;
        }

        return $this->resolveNativeReturnTypeFromClassAndMethod($className, $methodName, $staticCall);
    }

    public function resolvePropertyFetchType(PropertyFetch $propertyFetch): ?Type
    {
        $objectType = $this->getStaticType($propertyFetch->var);
        if (! $objectType instanceof TypeWithClassName) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        $propertyName = $this->nodeNameResolver->getName($propertyFetch);
        if ($propertyName === null) {
            return null;
        }

        if ($classReflection->hasProperty($propertyName)) {
            $propertyFetchScope = $propertyFetch->getAttribute(AttributeKey::SCOPE);
            $propertyReflection = $classReflection->getProperty($propertyName, $propertyFetchScope);

            if ($propertyReflection instanceof PhpPropertyReflection) {
                return $propertyReflection->getNativeType();
            }
        }

        return null;
    }

    public function resolveFuncCallReturnType(FuncCall $funcCall): ?Type
    {
        $funcCallScope = $funcCall->getAttribute(AttributeKey::SCOPE);

        $funcCallName = $funcCall->name;
        if ($funcCallName instanceof Expr) {
            return null;
        }

        if (! $this->reflectionProvider->hasFunction($funcCallName, $funcCallScope)) {
            return null;
        }

        $functionReflection = $this->reflectionProvider->getFunction($funcCallName, $funcCallScope);
        if (! $functionReflection instanceof PhpFunctionReflection) {
            return null;
        }

        return $this->privatesCaller->callPrivateMethod($functionReflection, 'getNativeReturnType', []);
    }

    private function resolveNativeReturnTypeFromClassAndMethod(string $className, string $methodName, Expr $expr): ?Type
    {
        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        if (! $classReflection->hasMethod($methodName)) {
            return null;
        }

        $callerScope = $expr->getAttribute(AttributeKey::SCOPE);

        $methodReflection = $classReflection->getMethod($methodName, $callerScope);
        if (! $methodReflection instanceof PhpMethodReflection) {
            return null;
        }

        return $this->privatesCaller->callPrivateMethod($methodReflection, 'getNativeReturnType', []);
    }
}

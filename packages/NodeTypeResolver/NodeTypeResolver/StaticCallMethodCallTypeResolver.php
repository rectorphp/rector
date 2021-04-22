<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class StaticCallMethodCallTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @required
     */
    public function autowireStaticCallMethodCallTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [StaticCall::class, MethodCall::class];
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function resolve(Node $node): Type
    {
        $methodName = $this->nodeNameResolver->getName($node->name);

        // no specific method found, return class types, e.g. <ClassType>::$method()
        if (! is_string($methodName)) {
            return new MixedType();
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return new MixedType();
        }

        $nodeReturnType = $scope->getType($node);

//        dump($node);
//        dump($nodeReturnType);
//        die;
//
        if (! $nodeReturnType instanceof MixedType) {
            return $nodeReturnType;
        }

        if ($node instanceof MethodCall) {
            $callerType = $this->nodeTypeResolver->resolve($node->var);
        } else {
            $callerType = $this->nodeTypeResolver->resolve($node->class);
        }

        foreach ($callerType->getReferencedClasses() as $referencedClass) {
            $classMethodReturnType = $this->resolveClassMethodReturnType($referencedClass, $methodName, $scope);
            if (! $classMethodReturnType instanceof MixedType) {
                return $classMethodReturnType;
            }
        }

        return new MixedType();
    }

    private function resolveClassMethodReturnType(string $referencedClass, string $methodName, Scope $scope): Type
    {
        if (! $this->reflectionProvider->hasClass($referencedClass)) {
            return new MixedType();
        }

        $classReflection = $this->reflectionProvider->getClass($referencedClass);

        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if (! $ancestorClassReflection->hasMethod($methodName)) {
                continue;
            }

            $methodReflection = $ancestorClassReflection->getMethod($methodName, $scope);
            if ($methodReflection instanceof PhpMethodReflection) {
                $parametersAcceptor = $methodReflection->getVariants()[0];
                return $parametersAcceptor->getReturnType();
            }
        }

        return new MixedType();
    }
}

<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class StaticCallTypeResolver implements NodeTypeResolverInterface
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
    public function autowireStaticCallTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function resolve(Node $node): Type
    {
        $classType = $this->nodeTypeResolver->resolve($node->class);
        $methodName = $this->nodeNameResolver->getName($node->name);

        // no specific method found, return class types, e.g. <ClassType>::$method()
        if (! is_string($methodName)) {
            return $classType;
        }

        if (! $classType instanceof ObjectType) {
            return $classType;
        }

        if (! $this->reflectionProvider->hasClass($classType->getClassName())) {
            return $classType;
        }

        $classReflection = $this->reflectionProvider->getClass($classType->getClassName());

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return $classType;
        }

        /** @var ClassReflection[] $currentAndParentClassReflections */
        $currentAndParentClassReflections = array_merge([$classReflection], $classReflection->getParents());

        foreach ($currentAndParentClassReflections as $currentAndParentClassReflection) {
            if (! $currentAndParentClassReflection->hasMethod($methodName)) {
                continue;
            }

            return $scope->getType($node);
        }

        return $classType;
    }
}

<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ClassMethodAndCallMatcher
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isMethodLikeCallMatchingClassMethod(Node $node, ClassMethod $classMethod): bool
    {
        if ($node instanceof MethodCall) {
            return $this->isMethodCallMatchingClassMethod($node, $classMethod);
        }

        if ($node instanceof StaticCall) {
            return $this->isStaticCallMatchingClassMethod($node, $classMethod);
        }

        return false;
    }

    private function isMethodCallMatchingClassMethod(MethodCall $methodCall, ClassMethod $classMethod): bool
    {
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        /** @var string $classMethodName */
        $classMethodName = $this->nodeNameResolver->getName($classMethod);

        if (! $this->nodeNameResolver->isName($methodCall->name, $classMethodName)) {
            return false;
        }

        $objectType = new ObjectType($className);
        $callerStaticType = $this->nodeTypeResolver->getStaticType($methodCall->var);

        return $objectType->isSuperTypeOf($callerStaticType)
            ->yes();
    }

    private function isStaticCallMatchingClassMethod(StaticCall $staticCall, ClassMethod $classMethod): bool
    {
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        if (! $this->nodeNameResolver->isName($staticCall->name, $methodName)) {
            return false;
        }

        $objectType = new ObjectType($className);

        $callerStaticType = $this->nodeTypeResolver->resolve($staticCall->class);
        if ($callerStaticType instanceof MixedType) {
            return false;
        }

        return $objectType->isSuperTypeOf($callerStaticType)
            ->yes();
    }
}

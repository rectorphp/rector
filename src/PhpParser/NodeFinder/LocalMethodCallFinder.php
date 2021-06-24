<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class LocalMethodCallFinder
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeTypeResolver $nodeTypeResolver,
        private NodeNameResolver $nodeNameResolver,
    ) {
    }

    /**
     * @return MethodCall[]
     */
    public function match(ClassMethod $classMethod): array
    {
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return [];
        }

        $className = $this->nodeNameResolver->getName($class);
        if (! is_string($className)) {
            return [];
        }

        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstanceOf($class, MethodCall::class);

        $classMethodName = $this->nodeNameResolver->getName($classMethod);

        $matchingMethodCalls = [];

        foreach ($methodCalls as $methodCall) {
            $callerType = $this->nodeTypeResolver->resolve($methodCall->var);
            if (! $callerType instanceof TypeWithClassName) {
                continue;
            }

            if ($callerType->getClassName() !== $className) {
                continue;
            }

            if (! $this->nodeNameResolver->isName($methodCall->name, $classMethodName)) {
                continue;
            }

            $matchingMethodCalls[] = $methodCall;
        }

        return $matchingMethodCalls;
    }
}

<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Read-only utils for chain of MethodCall Node:
 * "$this->methodCall()->chainedMethodCall()"
 */
final class ChainMethodCallManipulator
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * Checks "$this->someMethod()->anotherMethod()"
     *
     * @param string[] $methods
     */
    public function isTypeAndChainCalls(Node $node, Type $type, array $methods): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        // node chaining is in reverse order than code
        $methods = array_reverse($methods);

        foreach ($methods as $method) {
            $activeMethodName = $this->nodeNameResolver->getName($node->name);
            if ($activeMethodName !== $method) {
                return false;
            }

            $node = $node->var;
            if ($node instanceof MethodCall) {
                continue;
            }
        }

        $variableType = $this->nodeTypeResolver->resolve($node);
        if ($variableType instanceof MixedType) {
            return false;
        }

        return $variableType->isSuperTypeOf($type)->yes();
    }
}

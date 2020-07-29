<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\MagicDisclosure\NodeAnalyzer\ChainMethodCallNodeAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Read-only utils for chain of MethodCall Node:
 * "$this->methodCall()->chainedMethodCall()"
 *
 * @deprecated Merge with
 * @see ChainMethodCallNodeAnalyzer
 *
 * Rename to FluentMethodCallManipulator
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

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
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

    public function resolveRootMethodCall(MethodCall $methodCall): ?MethodCall
    {
        $expression = $methodCall->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($expression === null) {
            return null;
        }

        return $this->betterNodeFinder->findFirst([$expression], function (Node $node): bool {
            if (! $node instanceof MethodCall) {
                return false;
            }

            return ! $node->var instanceof MethodCall;
        });
    }
}

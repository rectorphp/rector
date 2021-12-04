<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

/**
 * Utils for chain of MethodCall Node:
 * "$this->methodCall()->chainedMethodCall()"
 */
final class FluentChainMethodCallNodeAnalyzer
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly NodeTypeResolver $nodeTypeResolver,
    ) {
    }

    /**
     * @return string[]
     */
    public function collectMethodCallNamesInChain(MethodCall $desiredMethodCall): array
    {
        $methodCalls = $this->collectAllMethodCallsInChain($desiredMethodCall);

        $methodNames = [];
        foreach ($methodCalls as $methodCall) {
            $methodName = $this->nodeNameResolver->getName($methodCall->name);
            if ($methodName === null) {
                continue;
            }

            $methodNames[] = $methodName;
        }

        return $methodNames;
    }

    /**
     * @return MethodCall[]
     */
    public function collectAllMethodCallsInChain(MethodCall $methodCall): array
    {
        $chainMethodCalls = [$methodCall];

        // traverse up
        $currentNode = $methodCall->var;
        while ($currentNode instanceof MethodCall) {
            $chainMethodCalls[] = $currentNode;
            $currentNode = $currentNode->var;
        }

        // traverse down
        if (count($chainMethodCalls) === 1) {
            $currentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
            while ($currentNode instanceof MethodCall) {
                $chainMethodCalls[] = $currentNode;
                $currentNode = $currentNode->getAttribute(AttributeKey::PARENT_NODE);
            }
        }

        return $chainMethodCalls;
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

        $rootMethodCall = $this->resolveRootMethodCall($node);
        if (! $rootMethodCall instanceof MethodCall) {
            return false;
        }

        $rootMethodCallVarType = $this->nodeTypeResolver->getType($rootMethodCall->var);
        if (! $rootMethodCallVarType instanceof FullyQualifiedObjectType) {
            return false;
        }

        // node chaining is in reverse order than code
        $methods = array_reverse($methods);

        foreach ($methods as $method) {
            if (! $this->nodeNameResolver->isName($node->name, $method)) {
                return false;
            }

            $node = $node->var;
        }

        $variableType = $this->nodeTypeResolver->getType($node);
        if ($variableType instanceof MixedType) {
            return false;
        }

        return $variableType->isSuperTypeOf($type)
            ->yes();
    }

    public function resolveRootExpr(MethodCall $methodCall): Expr | Name
    {
        $callerNode = $methodCall->var;

        while ($callerNode instanceof MethodCall || $callerNode instanceof StaticCall) {
            $callerNode = $callerNode instanceof StaticCall ? $callerNode->class : $callerNode->var;
        }

        return $callerNode;
    }

    public function resolveRootMethodCall(MethodCall $methodCall): ?MethodCall
    {
        $callerNode = $methodCall->var;

        while ($callerNode instanceof MethodCall && $callerNode->var instanceof MethodCall) {
            $callerNode = $callerNode->var;
        }

        if ($callerNode instanceof MethodCall) {
            return $callerNode;
        }

        return null;
    }
}

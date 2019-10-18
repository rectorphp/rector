<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class MethodCallManipulator
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(NameResolver $nameResolver, CallableNodeTraverser $callableNodeTraverser)
    {
        $this->nameResolver = $nameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @return string[]
     */
    public function findMethodCallNamesOnVariable(Variable $variable): array
    {
        /** @var Node|null $parentNode */
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode === null) {
            return [];
        }

        $variableName = $this->nameResolver->getName($variable);
        if ($variableName === null) {
            return [];
        }

        $previousMethodCallNames = [];

        do {
            $methodCallNames = $this->collectMethodCallsOnVariableName($parentNode, $variableName);
            $previousMethodCallNames = array_merge($previousMethodCallNames, $methodCallNames);

            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        } while ($parentNode instanceof Node && ! $parentNode instanceof FunctionLike);

        return array_unique($previousMethodCallNames);
    }

    /**
     * @return string[]
     */
    private function collectMethodCallsOnVariableName(Node $node, string $variableName): array
    {
        $methodCallNames = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($node, function (Node $node) use (
            $variableName,
            &$methodCallNames
        ) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->nameResolver->isName($node->var, $variableName)) {
                return null;
            }

            $methodName = $this->nameResolver->getName($node->name);
            if ($methodName === null) {
                return null;
            }

            $methodCallNames[] = $methodName;

            return null;
        });

        return $methodCallNames;
    }
}

<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
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

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        NameResolver $nameResolver,
        CallableNodeTraverser $callableNodeTraverser,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->nameResolver = $nameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @return string[]
     */
    public function findMethodCallNamesOnVariable(Variable $variable): array
    {
        $methodCallsOnVariable = $this->findMethodCallsOnVariable($variable);

        $methodCallNamesOnVariable = [];
        foreach ($methodCallsOnVariable as $methodCallOnVariable) {
            $methodName = $this->nameResolver->getName($methodCallOnVariable);
            if ($methodName === null) {
                continue;
            }

            $methodCallNamesOnVariable[] = $methodName;
        }

        return array_unique($methodCallNamesOnVariable);
    }

    /**
     * @return MethodCall[]
     */
    public function findMethodCallsIncludingChain(MethodCall $methodCall): array
    {
        $chainMethodCalls = [];

        // 1. collect method chain call
        $currentMethodCallee = $methodCall->var;
        while ($currentMethodCallee instanceof MethodCall) {
            $chainMethodCalls[] = $currentMethodCallee;
            $currentMethodCallee = $currentMethodCallee->var;
        }

        // 2. collect on-same-variable calls
        $onVariableMethodCalls = [];
        if ($currentMethodCallee instanceof Variable) {
            $onVariableMethodCalls = $this->findMethodCallsOnVariable($currentMethodCallee);
        }

        $methodCalls = array_merge($chainMethodCalls, $onVariableMethodCalls);
        return $this->uniquateObjects($methodCalls);
    }

    public function findAssignToVariable(Variable $variable): ?Assign
    {
        /** @var Node|null $parentNode */
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode === null) {
            return null;
        }

        $variableName = $this->nameResolver->getName($variable);
        if ($variableName === null) {
            return null;
        }

        do {
            $assign = $this->findAssignToVariableName($parentNode, $variableName);
            if ($assign !== null) {
                return $assign;
            }

            $parentNode = $this->resolvePreviousNodeInSameScope($parentNode);
        } while ($parentNode instanceof Node && ! $parentNode instanceof FunctionLike);

        return null;
    }

    /**
     * @return MethodCall[]
     */
    private function findMethodCallsOnVariable(Variable $variable): array
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

        $previousMethodCalls = [];

        do {
            $methodCalls = $this->collectMethodCallsOnVariableName($parentNode, $variableName);
            $previousMethodCalls = array_merge($previousMethodCalls, $methodCalls);

            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        } while ($parentNode instanceof Node && ! $parentNode instanceof FunctionLike);

        return $previousMethodCalls;
    }

    /**
     * @see https://stackoverflow.com/a/4507991/1348344
     * @param object[] $objects
     * @return object[]
     */
    private function uniquateObjects(array $objects): array
    {
        $uniqueObjects = [];
        foreach ($objects as $object) {
            if (in_array($object, $uniqueObjects, true)) {
                continue;
            }

            $uniqueObjects[] = $object;
        }

        // re-index
        return array_values($uniqueObjects);
    }

    private function findAssignToVariableName(Node $node, string $variableName): ?Assign
    {
        return $this->betterNodeFinder->findFirst($node, function (Node $node) use ($variableName): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            if (! $node->var instanceof Variable) {
                return false;
            }

            return $this->nameResolver->isName($node->var, $variableName);
        });
    }

    private function resolvePreviousNodeInSameScope(Node $parentNode): ?Node
    {
        $previousParentNode = $parentNode;
        $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);

        if (! $parentNode instanceof FunctionLike) {
            // is about to leave → try previous expression
            $previousExpression = $previousParentNode->getAttribute(AttributeKey::PREVIOUS_EXPRESSION);
            if ($previousExpression instanceof Expression) {
                return $previousExpression->expr;
            }
        }

        return $parentNode;
    }

    /**
     * @return MethodCall[]
     */
    private function collectMethodCallsOnVariableName(Node $node, string $variableName): array
    {
        $methodCalls = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($node, function (Node $node) use (
            $variableName,
            &$methodCalls
        ) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $node->var instanceof Variable) {
                return null;
            }

            if (! $this->nameResolver->isName($node->var, $variableName)) {
                return null;
            }

            $methodCalls[] = $node;

            return null;
        });

        return $methodCalls;
    }
}

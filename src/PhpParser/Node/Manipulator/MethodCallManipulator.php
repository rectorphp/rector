<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MethodCallManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        CallableNodeTraverser $callableNodeTraverser,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
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
            $methodName = $this->nodeNameResolver->getName($methodCallOnVariable);
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

        $variableName = $this->nodeNameResolver->getName($variable);
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

        $variableName = $this->nodeNameResolver->getName($variable);
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
     *
     * @template T
     * @phpstan-param array<T>|T[] $objects
     * @phpstan-return array<T>|T[]
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
        /** @var Assign|null $assign */
        $assign = $this->betterNodeFinder->findFirst($node, function (Node $node) use ($variableName): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            if (! $node->var instanceof Variable) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->var, $variableName);
        });

        return $assign;
    }

    private function resolvePreviousNodeInSameScope(Node $parentNode): ?Node
    {
        $previousParentNode = $parentNode;
        $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);

        if (! $parentNode instanceof FunctionLike) {
            // is about to leave → try previous expression
            $previousStatement = $previousParentNode->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
            if ($previousStatement instanceof Expression) {
                return $previousStatement->expr;
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

            if (! $this->nodeNameResolver->isName($node->var, $variableName)) {
                return null;
            }

            $methodCalls[] = $node;

            return null;
        });

        return $methodCalls;
    }
}

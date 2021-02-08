<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MethodCallManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }

    /**
     * @return string[]
     */
    public function findMethodCallNamesOnVariable(Variable $variable): array
    {
        $methodCallsOnVariable = $this->findMethodCallsOnVariable($variable);

        $methodCallNamesOnVariable = [];
        foreach ($methodCallsOnVariable as $methodCallOnVariable) {
            $methodName = $this->nodeNameResolver->getName($methodCallOnVariable->name);
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
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Node) {
            return null;
        }

        $variableName = $this->nodeNameResolver->getName($variable);
        if ($variableName === null) {
            return null;
        }

        do {
            $assign = $this->findAssignToVariableName($parentNode, $variableName);
            if ($assign instanceof Assign) {
                return $assign;
            }

            $parentNode = $this->resolvePreviousNodeInSameScope($parentNode);
        } while ($parentNode instanceof Node && ! $parentNode instanceof FunctionLike);

        return null;
    }

    /**
     * @return MethodCall[]
     */
    public function findMethodCallsOnVariable(Variable $variable): array
    {
        // get scope node, e.g. parent function call, method call or anonymous function
        $classMethod = $variable->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof ClassMethod) {
            return [];
        }

        $variableName = $this->nodeNameResolver->getName($variable);

        return $this->betterNodeFinder->find((array) $classMethod->stmts, function (Node $node) use (
            $variableName
        ): bool {
            if (! $node instanceof MethodCall) {
                return false;
            }

            // cover fluent interfaces too
            $callerNode = $this->fluentChainMethodCallNodeAnalyzer->resolveRootExpr($node);
            if (! $callerNode instanceof Variable) {
                return false;
            }

            return $this->nodeNameResolver->isName($callerNode, $variableName);
        });
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

    private function findAssignToVariableName(Node $node, string $variableName): ?Node
    {
        return $this->betterNodeFinder->findFirst($node, function (Node $node) use ($variableName): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            if (! $node->var instanceof Variable) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->var, $variableName);
        });
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
}

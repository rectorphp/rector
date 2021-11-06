<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

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
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }
    /**
     * @return string[]
     */
    public function findMethodCallNamesOnVariable(\PhpParser\Node\Expr\Variable $variable) : array
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
        return \array_unique($methodCallNamesOnVariable);
    }
    /**
     * @return MethodCall[]
     */
    public function findMethodCallsIncludingChain(\PhpParser\Node\Expr\MethodCall $methodCall) : array
    {
        $chainMethodCalls = [];
        // 1. collect method chain call
        $currentMethodCallee = $methodCall->var;
        while ($currentMethodCallee instanceof \PhpParser\Node\Expr\MethodCall) {
            $chainMethodCalls[] = $currentMethodCallee;
            $currentMethodCallee = $currentMethodCallee->var;
        }
        // 2. collect on-same-variable calls
        $onVariableMethodCalls = [];
        if ($currentMethodCallee instanceof \PhpParser\Node\Expr\Variable) {
            $onVariableMethodCalls = $this->findMethodCallsOnVariable($currentMethodCallee);
        }
        $methodCalls = \array_merge($chainMethodCalls, $onVariableMethodCalls);
        return $this->uniquateObjects($methodCalls);
    }
    public function findAssignToVariable(\PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node\Expr\Assign
    {
        $parentNode = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node) {
            return null;
        }
        $variableName = $this->nodeNameResolver->getName($variable);
        if ($variableName === null) {
            return null;
        }
        do {
            $assign = $this->findAssignToVariableName($parentNode, $variableName);
            if ($assign instanceof \PhpParser\Node\Expr\Assign) {
                return $assign;
            }
            $parentNode = $this->resolvePreviousNodeInSameScope($parentNode);
        } while ($parentNode instanceof \PhpParser\Node && !$parentNode instanceof \PhpParser\Node\FunctionLike);
        return null;
    }
    /**
     * @return MethodCall[]
     */
    public function findMethodCallsOnVariable(\PhpParser\Node\Expr\Variable $variable) : array
    {
        // get scope node, e.g. parent function call, method call or anonymous function
        $classMethod = $this->betterNodeFinder->findParentType($variable, \PhpParser\Node\Stmt\ClassMethod::class);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        $variableName = $this->nodeNameResolver->getName($variable);
        if ($variableName === null) {
            return [];
        }
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstanceOf($classMethod, \PhpParser\Node\Expr\MethodCall::class);
        return \array_filter($methodCalls, function (\PhpParser\Node\Expr\MethodCall $methodCall) use($variableName) : bool {
            // cover fluent interfaces too
            $callerNode = $this->fluentChainMethodCallNodeAnalyzer->resolveRootExpr($methodCall);
            if (!$callerNode instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
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
    private function uniquateObjects(array $objects) : array
    {
        $uniqueObjects = [];
        foreach ($objects as $object) {
            if (\in_array($object, $uniqueObjects, \true)) {
                continue;
            }
            $uniqueObjects[] = $object;
        }
        // re-index
        return \array_values($uniqueObjects);
    }
    private function findAssignToVariableName(\PhpParser\Node $node, string $variableName) : ?\PhpParser\Node
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Expr\Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($assign->var, $variableName)) {
                continue;
            }
            return $assign;
        }
        return null;
    }
    private function resolvePreviousNodeInSameScope(\PhpParser\Node $parentNode) : ?\PhpParser\Node
    {
        $previousParentNode = $parentNode;
        $parentNode = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\FunctionLike) {
            // is about to leave → try previous expression
            $previousStatement = $previousParentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
            if ($previousStatement instanceof \PhpParser\Node\Stmt\Expression) {
                return $previousStatement->expr;
            }
        }
        return $parentNode;
    }
}

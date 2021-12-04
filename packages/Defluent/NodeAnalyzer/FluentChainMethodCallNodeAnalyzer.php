<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return string[]
     */
    public function collectMethodCallNamesInChain(\PhpParser\Node\Expr\MethodCall $desiredMethodCall) : array
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
    public function collectAllMethodCallsInChain(\PhpParser\Node\Expr\MethodCall $methodCall) : array
    {
        $chainMethodCalls = [$methodCall];
        // traverse up
        $currentNode = $methodCall->var;
        while ($currentNode instanceof \PhpParser\Node\Expr\MethodCall) {
            $chainMethodCalls[] = $currentNode;
            $currentNode = $currentNode->var;
        }
        // traverse down
        if (\count($chainMethodCalls) === 1) {
            $currentNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            while ($currentNode instanceof \PhpParser\Node\Expr\MethodCall) {
                $chainMethodCalls[] = $currentNode;
                $currentNode = $currentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            }
        }
        return $chainMethodCalls;
    }
    /**
     * Checks "$this->someMethod()->anotherMethod()"
     *
     * @param string[] $methods
     */
    public function isTypeAndChainCalls(\PhpParser\Node $node, \PHPStan\Type\Type $type, array $methods) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        $rootMethodCall = $this->resolveRootMethodCall($node);
        if (!$rootMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        $rootMethodCallVarType = $this->nodeTypeResolver->getType($rootMethodCall->var);
        if (!$rootMethodCallVarType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
            return \false;
        }
        // node chaining is in reverse order than code
        $methods = \array_reverse($methods);
        foreach ($methods as $method) {
            if (!$this->nodeNameResolver->isName($node->name, $method)) {
                return \false;
            }
            $node = $node->var;
        }
        $variableType = $this->nodeTypeResolver->getType($node);
        if ($variableType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        return $variableType->isSuperTypeOf($type)->yes();
    }
    /**
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Name
     */
    public function resolveRootExpr(\PhpParser\Node\Expr\MethodCall $methodCall)
    {
        $callerNode = $methodCall->var;
        while ($callerNode instanceof \PhpParser\Node\Expr\MethodCall || $callerNode instanceof \PhpParser\Node\Expr\StaticCall) {
            $callerNode = $callerNode instanceof \PhpParser\Node\Expr\StaticCall ? $callerNode->class : $callerNode->var;
        }
        return $callerNode;
    }
    public function resolveRootMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        $callerNode = $methodCall->var;
        while ($callerNode instanceof \PhpParser\Node\Expr\MethodCall && $callerNode->var instanceof \PhpParser\Node\Expr\MethodCall) {
            $callerNode = $callerNode->var;
        }
        if ($callerNode instanceof \PhpParser\Node\Expr\MethodCall) {
            return $callerNode;
        }
        return null;
    }
}

<?php

declare (strict_types=1);
namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Defluent\Reflection\MethodCallToClassMethodParser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
/**
 * Utils for chain of MethodCall Node:
 * "$this->methodCall()->chainedMethodCall()"
 */
final class FluentChainMethodCallNodeAnalyzer
{
    /**
     * Types that look like fluent interface, but actually create a new object.
     * Should be skipped, as they return different object. Not an fluent interface!
     *
     * @var class-string<MutatingScope>[]
     */
    private const KNOWN_FACTORY_FLUENT_TYPES = ['PHPStan\\Analyser\\MutatingScope'];
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var NodeFinder
     */
    private $nodeFinder;
    /**
     * @var MethodCallToClassMethodParser
     */
    private $methodCallToClassMethodParser;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \PhpParser\NodeFinder $nodeFinder, \Rector\Defluent\Reflection\MethodCallToClassMethodParser $methodCallToClassMethodParser)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFinder = $nodeFinder;
        $this->methodCallToClassMethodParser = $methodCallToClassMethodParser;
    }
    /**
     * Checks that in:
     * $this->someCall();
     *
     * The method is fluent class method === returns self
     * public function someClassMethod()
     * {
     *      return $this;
     * }
     */
    public function isFluentClassMethodOfMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if ($this->isCall($methodCall->var)) {
            return \false;
        }
        $calleeStaticType = $this->nodeTypeResolver->getStaticType($methodCall->var);
        // we're not sure
        if ($calleeStaticType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        $methodReturnStaticType = $this->nodeTypeResolver->getStaticType($methodCall);
        // is fluent type
        if (!$calleeStaticType->equals($methodReturnStaticType)) {
            return \false;
        }
        if ($calleeStaticType instanceof \PHPStan\Type\ObjectType) {
            foreach (self::KNOWN_FACTORY_FLUENT_TYPES as $knownFactoryFluentType) {
                if ($calleeStaticType->isInstanceOf($knownFactoryFluentType)->yes()) {
                    return \false;
                }
            }
        }
        return !$this->isMethodCallCreatingNewInstance($methodCall);
    }
    public function isLastChainMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        // is chain method call
        if (!$methodCall->var instanceof \PhpParser\Node\Expr\MethodCall && !$methodCall->var instanceof \PhpParser\Node\Expr\New_) {
            return \false;
        }
        $nextNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        // is last chain call
        return !$nextNode instanceof \PhpParser\Node;
    }
    /**
     * @return string[]|null[]
     */
    public function collectMethodCallNamesInChain(\PhpParser\Node\Expr\MethodCall $desiredMethodCall) : array
    {
        $methodCalls = $this->collectAllMethodCallsInChain($desiredMethodCall);
        $methodNames = [];
        foreach ($methodCalls as $methodCall) {
            $methodNames[] = $this->nodeNameResolver->getName($methodCall->name);
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
     * @return MethodCall[]
     */
    public function collectAllMethodCallsInChainWithoutRootOne(\PhpParser\Node\Expr\MethodCall $methodCall) : array
    {
        $chainMethodCalls = $this->collectAllMethodCallsInChain($methodCall);
        foreach ($chainMethodCalls as $key => $chainMethodCall) {
            if (!$chainMethodCall->var instanceof \PhpParser\Node\Expr\MethodCall && !$chainMethodCall->var instanceof \PhpParser\Node\Expr\New_) {
                unset($chainMethodCalls[$key]);
                break;
            }
        }
        return \array_values($chainMethodCalls);
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
        // node chaining is in reverse order than code
        $methods = \array_reverse($methods);
        foreach ($methods as $method) {
            if (!$this->nodeNameResolver->isName($node->name, $method)) {
                return \false;
            }
            $node = $node->var;
        }
        $variableType = $this->nodeTypeResolver->resolve($node);
        if ($variableType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        return $variableType->isSuperTypeOf($type)->yes();
    }
    public function resolveRootExpr(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node
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
    private function isCall(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return \true;
        }
        return $expr instanceof \PhpParser\Node\Expr\StaticCall;
    }
    private function isMethodCallCreatingNewInstance(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        $classMethod = $this->methodCallToClassMethodParser->parseMethodCall($methodCall);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        /** @var Return_[] $returns */
        $returns = $this->nodeFinder->findInstanceOf($classMethod, \PhpParser\Node\Stmt\Return_::class);
        foreach ($returns as $return) {
            if (!$return->expr instanceof \PhpParser\Node\Expr\New_) {
                continue;
            }
            $new = $return->expr;
            if ($this->nodeNameResolver->isName($new->class, 'self')) {
                return \true;
            }
        }
        return \false;
    }
}

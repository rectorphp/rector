<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
final class PropertyFetchAnalyzer
{
    /**
     * @var string
     */
    private const THIS = 'this';
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->astResolver = $astResolver;
    }
    public function isLocalPropertyFetch(\PhpParser\Node $node) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
            if ($node->var instanceof \PhpParser\Node\Expr\MethodCall) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node->var, self::THIS);
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            return $this->nodeNameResolver->isName($node->class, \Rector\Core\Enum\ObjectReference::SELF()->getValue());
        }
        return \false;
    }
    public function isLocalPropertyFetchName(\PhpParser\Node $node, string $desiredPropertyName) : bool
    {
        if (!$this->isLocalPropertyFetch($node)) {
            return \false;
        }
        /** @var PropertyFetch|StaticPropertyFetch $node */
        return $this->nodeNameResolver->isName($node->name, $desiredPropertyName);
    }
    public function containsLocalPropertyFetchName(\PhpParser\Node $node, string $propertyName) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($node, function (\PhpParser\Node $node) use($propertyName) : bool {
            return $this->isLocalPropertyFetchName($node, $propertyName);
        });
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $expr
     */
    public function isPropertyToSelf($expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\PropertyFetch && !$this->nodeNameResolver->isName($expr->var, self::THIS)) {
            return \false;
        }
        if ($expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch && !$this->nodeNameResolver->isName($expr->class, \Rector\Core\Enum\ObjectReference::SELF()->getValue())) {
            return \false;
        }
        $class = $this->betterNodeFinder->findParentType($expr, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        foreach ($class->getProperties() as $property) {
            if (!$this->nodeNameResolver->areNamesEqual($property->props[0], $expr)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    public function isPropertyFetch(\PhpParser\Node $node) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \true;
        }
        return $node instanceof \PhpParser\Node\Expr\StaticPropertyFetch;
    }
    /**
     * Matches:
     * "$this->someValue = $<variableName>;"
     */
    public function isVariableAssignToThisPropertyFetch(\PhpParser\Node $node, string $variableName) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        if (!$node->expr instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->expr, $variableName)) {
            return \false;
        }
        return $this->isLocalPropertyFetch($node->var);
    }
    public function isFilledViaMethodCallInConstructStmts(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        $class = $this->betterNodeFinder->findParentType($propertyFetch, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        $construct = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$construct instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($construct, [\PhpParser\Node\Expr\MethodCall::class]);
        foreach ($methodCalls as $methodCall) {
            if (!$methodCall->var instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($methodCall->var, self::THIS)) {
                continue;
            }
            $classMethod = $this->astResolver->resolveClassMethodFromMethodCall($methodCall);
            if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            $isFound = $this->isPropertyAssignFoundInClassMethod($classMethod, $propertyFetch);
            if (!$isFound) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param string[] $propertyNames
     */
    public function isLocalPropertyOfNames(\PhpParser\Node $node, array $propertyNames) : bool
    {
        if (!$this->isLocalPropertyFetch($node)) {
            return \false;
        }
        /** @var PropertyFetch $node */
        return $this->nodeNameResolver->isNames($node->name, $propertyNames);
    }
    private function isPropertyAssignFoundInClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($classMethod, function (\PhpParser\Node $subNode) use($propertyFetch) : bool {
            if (!$subNode instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            if (!$subNode->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($propertyFetch, $subNode->var);
        });
    }
}

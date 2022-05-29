<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\ObjectType;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\AstResolver;
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
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->astResolver = $astResolver;
    }
    public function isLocalPropertyFetch(\PhpParser\Node $node) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
            if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node->var, self::THIS);
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            if (!$node->class instanceof \PhpParser\Node\Name) {
                return \false;
            }
            return $this->nodeNameResolver->isNames($node->class, [\Rector\Core\Enum\ObjectReference::SELF, \Rector\Core\Enum\ObjectReference::STATIC]);
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
        if ($expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch && !$this->nodeNameResolver->isName($expr->class, \Rector\Core\Enum\ObjectReference::SELF)) {
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
    public function isFilledViaMethodCallInConstructStmts(\PhpParser\Node\Stmt\ClassLike $classLike, string $propertyName) : bool
    {
        $classMethod = $classLike->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        $stmts = (array) $classMethod->stmts;
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$stmt->expr instanceof \PhpParser\Node\Expr\MethodCall && !$stmt->expr instanceof \PhpParser\Node\Expr\StaticCall) {
                continue;
            }
            $callerClassMethod = $this->astResolver->resolveClassMethodFromCall($stmt->expr);
            if (!$callerClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            $callerClass = $this->betterNodeFinder->findParentType($callerClassMethod, \PhpParser\Node\Stmt\Class_::class);
            if (!$callerClass instanceof \PhpParser\Node\Stmt\Class_) {
                continue;
            }
            $callerClassName = (string) $this->nodeNameResolver->getName($callerClass);
            $isFound = $this->isPropertyAssignFoundInClassMethod($classLike, $className, $callerClassName, $callerClassMethod, $propertyName);
            if ($isFound) {
                return \true;
            }
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
    private function isPropertyAssignFoundInClassMethod(\PhpParser\Node\Stmt\ClassLike $classLike, string $className, string $callerClassName, \PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : bool
    {
        if ($className !== $callerClassName && !$classLike instanceof \PhpParser\Node\Stmt\Trait_) {
            $objectType = new \PHPStan\Type\ObjectType($className);
            $callerObjectType = new \PHPStan\Type\ObjectType($callerClassName);
            if (!$callerObjectType->isSuperTypeOf($objectType)->yes()) {
                return \false;
            }
        }
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            if ($this->isLocalPropertyFetchName($stmt->expr->var, $propertyName)) {
                return \true;
            }
        }
        return \false;
    }
}

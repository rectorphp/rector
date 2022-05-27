<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class SetterClassMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function matchNullalbeClassMethodProperty(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\Property
    {
        $propertyFetch = $this->matchNullalbeClassMethodPropertyFetch($classMethod);
        if (!$propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetch);
        if (!$phpPropertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
            return null;
        }
        $classLike = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $propertyName = (string) $this->nodeNameResolver->getName($propertyFetch->name);
        return $classLike->getProperty($propertyName);
    }
    /**
     * Matches:
     *
     * public function setSomething(?Type $someValue); { <$this->someProperty> = $someValue; }
     */
    private function matchNullalbeClassMethodPropertyFetch(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Expr\PropertyFetch
    {
        $propertyFetch = $this->matchSetterOnlyPropertyFetch($classMethod);
        if (!$propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        // is nullable param
        $onlyParam = $classMethod->params[0];
        if (!$this->nodeTypeResolver->isNullableTypeOfSpecificType($onlyParam, \PHPStan\Type\ObjectType::class)) {
            return null;
        }
        return $propertyFetch;
    }
    private function matchSetterOnlyPropertyFetch(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Expr\PropertyFetch
    {
        if (\count($classMethod->params) !== 1) {
            return null;
        }
        $stmts = (array) $classMethod->stmts;
        if (\count($stmts) !== 1) {
            return null;
        }
        $onlyStmt = $stmts[0] ?? null;
        if (!$onlyStmt instanceof \PhpParser\Node\Stmt) {
            return null;
        }
        if ($onlyStmt instanceof \PhpParser\Node\Stmt\Expression) {
            $onlyStmt = $onlyStmt->expr;
        }
        if (!$onlyStmt instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        if (!$onlyStmt->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        $propertyFetch = $onlyStmt->var;
        if (!$this->isVariableName($propertyFetch->var, 'this')) {
            return null;
        }
        return $propertyFetch;
    }
    private function isVariableName(?\PhpParser\Node $node, string $name) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $this->nodeNameResolver->isName($node, $name);
    }
}

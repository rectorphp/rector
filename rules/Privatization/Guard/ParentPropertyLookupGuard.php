<?php

declare (strict_types=1);
namespace Rector\Privatization\Guard;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class ParentPropertyLookupGuard
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->astResolver = $astResolver;
    }
    public function isLegal(\PhpParser\Node\Stmt\Property $property) : bool
    {
        $class = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if ($class->extends === null) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        $className = $classReflection->getName();
        $parents = $classReflection->getParents();
        // parent class not autoloaded
        if ($parents === []) {
            return \false;
        }
        foreach ($parents as $parent) {
            if ($parent->hasProperty($propertyName)) {
                return \false;
            }
            if ($this->isFoundInParentClassMethods($parent, $propertyName, $className)) {
                return \false;
            }
        }
        return \true;
    }
    private function isFoundInParentClassMethods(\PHPStan\Reflection\ClassReflection $parentClassReflection, string $propertyName, string $className) : bool
    {
        $classLike = $this->astResolver->resolveClassFromName($parentClassReflection->getName());
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        $methods = $classLike->getMethods();
        foreach ($methods as $method) {
            $isFound = $this->isFoundInMethodStmts((array) $method->stmts, $propertyName, $className);
            if ($isFound) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isFoundInMethodStmts(array $stmts, string $propertyName, string $className) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (\PhpParser\Node $subNode) use($propertyName, $className) : bool {
            if (!$this->propertyFetchAnalyzer->isPropertyFetch($subNode)) {
                return \false;
            }
            /** @var PropertyFetch|StaticPropertyFetch $subNode */
            if ($subNode instanceof \PhpParser\Node\Expr\PropertyFetch) {
                if (!$subNode->var instanceof \PhpParser\Node\Expr\Variable) {
                    return \false;
                }
                if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                    return \false;
                }
                return $this->nodeNameResolver->isName($subNode, $propertyName);
            }
            if (!$this->nodeNameResolver->isNames($subNode->class, [\Rector\Core\Enum\ObjectReference::SELF, \Rector\Core\Enum\ObjectReference::STATIC, $className])) {
                return \false;
            }
            return $this->nodeNameResolver->isName($subNode->name, $propertyName);
        });
    }
}

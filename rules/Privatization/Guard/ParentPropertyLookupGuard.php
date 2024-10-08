<?php

declare (strict_types=1);
namespace Rector\Privatization\Guard;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Enum\ObjectReference;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeManipulator\PropertyManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Reflection\ClassReflectionAnalyzer;
final class ParentPropertyLookupGuard
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Reflection\ClassReflectionAnalyzer
     */
    private $classReflectionAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer, AstResolver $astResolver, PropertyManipulator $propertyManipulator, ClassReflectionAnalyzer $classReflectionAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->astResolver = $astResolver;
        $this->propertyManipulator = $propertyManipulator;
        $this->classReflectionAnalyzer = $classReflectionAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|string $property
     */
    public function isLegal($property, ?ClassReflection $classReflection) : bool
    {
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if ($classReflection->isAnonymous()) {
            return \false;
        }
        $propertyName = $property instanceof Property ? $this->nodeNameResolver->getName($property) : $property;
        if ($this->propertyManipulator->isUsedByTrait($classReflection, $propertyName)) {
            return \false;
        }
        $parentClassName = $this->classReflectionAnalyzer->resolveParentClassName($classReflection);
        if ($parentClassName === null) {
            return \true;
        }
        $className = $classReflection->getName();
        $parentClassReflections = $classReflection->getParents();
        // parent class not autoloaded
        if ($parentClassReflections === []) {
            return \false;
        }
        return $this->isGuardedByParents($parentClassReflections, $propertyName, $className);
    }
    private function isFoundInParentClassMethods(ClassReflection $parentClassReflection, string $propertyName, string $className) : bool
    {
        $classLike = $this->astResolver->resolveClassFromClassReflection($parentClassReflection);
        if (!$classLike instanceof Class_) {
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
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $subNode) use($propertyName, $className) : bool {
            if (!$this->propertyFetchAnalyzer->isPropertyFetch($subNode)) {
                return \false;
            }
            if ($subNode instanceof PropertyFetch) {
                if (!$subNode->var instanceof Variable) {
                    return \false;
                }
                if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                    return \false;
                }
                return $this->nodeNameResolver->isName($subNode, $propertyName);
            }
            if (!$this->nodeNameResolver->isNames($subNode->class, [ObjectReference::SELF, ObjectReference::STATIC, $className])) {
                return \false;
            }
            return $this->nodeNameResolver->isName($subNode->name, $propertyName);
        });
    }
    /**
     * @param ClassReflection[] $parentClassReflections
     */
    private function isGuardedByParents(array $parentClassReflections, string $propertyName, string $className) : bool
    {
        foreach ($parentClassReflections as $parentClassReflection) {
            if ($parentClassReflection->hasProperty($propertyName)) {
                return \false;
            }
            if ($this->isFoundInParentClassMethods($parentClassReflection, $propertyName, $className)) {
                return \false;
            }
        }
        return \true;
    }
}

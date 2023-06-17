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
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\PhpParser\ClassLikeAstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\Reflection\PrivatesAccessor;
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
     * @var \Rector\Core\PhpParser\ClassLikeAstResolver
     */
    private $classLikeAstResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Core\Util\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer, ClassLikeAstResolver $classLikeAstResolver, PropertyManipulator $propertyManipulator, PrivatesAccessor $privatesAccessor)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->classLikeAstResolver = $classLikeAstResolver;
        $this->propertyManipulator = $propertyManipulator;
        $this->privatesAccessor = $privatesAccessor;
    }
    public function isLegal(Property $property, ?ClassReflection $classReflection) : bool
    {
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if ($classReflection->isAnonymous()) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->propertyManipulator->isUsedByTrait($classReflection, $propertyName)) {
            return \false;
        }
        $nativeReflection = $classReflection->getNativeReflection();
        $betterReflectionClass = $this->privatesAccessor->getPrivateProperty($nativeReflection, 'betterReflectionClass');
        $parentClassName = $this->privatesAccessor->getPrivateProperty($betterReflectionClass, 'parentClassName');
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
        $classLike = $this->classLikeAstResolver->resolveClassFromClassReflection($parentClassReflection);
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
            /** @var PropertyFetch|StaticPropertyFetch $subNode */
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

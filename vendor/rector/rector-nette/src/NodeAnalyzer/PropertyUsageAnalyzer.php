<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
final class PropertyUsageAnalyzer
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, FamilyRelationsAnalyzer $familyRelationsAnalyzer, NodeNameResolver $nodeNameResolver, AstResolver $astResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isPropertyFetchedInChildClass(Property $property) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);
        if (!$classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }
        if ($classReflection->isClass() && $classReflection->isFinal()) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        foreach ($childrenClassReflections as $childClassReflection) {
            $childClass = $this->astResolver->resolveClassFromName($childClassReflection->getName());
            if (!$childClass instanceof Class_) {
                continue;
            }
            $isPropertyFetched = (bool) $this->betterNodeFinder->findFirst($childClass->stmts, function (Node $node) use($propertyName) : bool {
                return $this->propertyFetchAnalyzer->isLocalPropertyFetchName($node, $propertyName);
            });
            if ($isPropertyFetched) {
                return \true;
            }
        }
        return \false;
    }
}

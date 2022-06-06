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
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isPropertyFetchedInChildClass(\PhpParser\Node\Stmt\Property $property) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($classReflection->isClass() && $classReflection->isFinal()) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        foreach ($childrenClassReflections as $childClassReflection) {
            $childClass = $this->astResolver->resolveClassFromName($childClassReflection->getName());
            if (!$childClass instanceof \PhpParser\Node\Stmt\Class_) {
                continue;
            }
            $isPropertyFetched = (bool) $this->betterNodeFinder->findFirst($childClass->stmts, function (\PhpParser\Node $node) use($propertyName) : bool {
                return $this->propertyFetchAnalyzer->isLocalPropertyFetchName($node, $propertyName);
            });
            if ($isPropertyFetched) {
                return \true;
            }
        }
        return \false;
    }
}

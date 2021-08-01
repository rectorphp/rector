<?php

declare (strict_types=1);
namespace Rector\FamilyTree\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
    }
    public function isPropertyFetchedInChildClass(\PhpParser\Node\Stmt\Property $property) : bool
    {
        $className = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            return \false;
        }
        $scope = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
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
                return $this->nodeNameResolver->isLocalPropertyFetchNamed($node, $propertyName);
            });
            if ($isPropertyFetched) {
                return \true;
            }
        }
        return \false;
    }
}

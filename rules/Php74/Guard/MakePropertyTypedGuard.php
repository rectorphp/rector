<?php

declare (strict_types=1);
namespace Rector\Php74\Guard;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\NodeAnalyzer\PropertyAnalyzer;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class MakePropertyTypedGuard
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
     * @var \Rector\Core\NodeAnalyzer\PropertyAnalyzer
     */
    private $propertyAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeAnalyzer\PropertyAnalyzer $propertyAnalyzer, \Rector\Core\NodeManipulator\PropertyManipulator $propertyManipulator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyAnalyzer = $propertyAnalyzer;
        $this->propertyManipulator = $propertyManipulator;
    }
    public function isLegal(\PhpParser\Node\Stmt\Property $property, bool $inlinePublic = \true) : bool
    {
        if ($property->type !== null) {
            return \false;
        }
        if (\count($property->props) > 1) {
            return \false;
        }
        $scope = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        /**
         * - trait properties are unpredictable based on class context they appear in
         * - on interface properties as well, as interface not allowed to have property
         */
        $class = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->propertyManipulator->isUsedByTrait($class, $propertyName)) {
            return \false;
        }
        if ($inlinePublic) {
            return !$this->propertyAnalyzer->hasForbiddenType($property);
        }
        if ($property->isPrivate()) {
            return !$this->propertyAnalyzer->hasForbiddenType($property);
        }
        return $this->isSafeProtectedProperty($property, $class);
    }
    private function isSafeProtectedProperty(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Stmt\Class_ $class) : bool
    {
        if (!$property->isProtected()) {
            return \false;
        }
        if (!$class->isFinal()) {
            return \false;
        }
        return !$class->extends instanceof \PhpParser\Node\Name\FullyQualified;
    }
}

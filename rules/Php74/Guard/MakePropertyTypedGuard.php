<?php

declare(strict_types=1);

namespace Rector\Php74\Guard;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\NodeAnalyzer\PropertyAnalyzer;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MakePropertyTypedGuard
{
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly AstResolver $astResolver,
        private readonly PropertyFetchAnalyzer $propertyFetchAnalyzer,
        private readonly PropertyAnalyzer $propertyAnalyzer
    ) {
    }

    public function isLegal(Property $property, bool $inlinePublic = true): bool
    {
        if ($property->type !== null) {
            return false;
        }

        if (count($property->props) > 1) {
            return false;
        }

        $scope = $property->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        /**
         * - trait properties are unpredictable based on class context they appear in
         * - on interface properties as well, as interface not allowed to have property
         */
        $class = $this->betterNodeFinder->findParentType($property, Class_::class);
        if (! $class instanceof Class_) {
            return false;
        }

        $propertyName = $this->nodeNameResolver->getName($property);

        if ($this->isModifiedByTrait($class, $propertyName)) {
            return false;
        }

        if ($inlinePublic) {
            return ! $this->propertyAnalyzer->hasForbiddenType($property);
        }

        if ($property->isPrivate()) {
            return ! $this->propertyAnalyzer->hasForbiddenType($property);
        }

        return $this->isSafeProtectedProperty($property, $class);
    }

    private function isModifiedByTrait(Class_ $class, string $propertyName): bool
    {
        foreach ($class->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $traitName) {
                $trait = $this->astResolver->resolveClassFromName($traitName->toString());
                if (! $trait instanceof Trait_) {
                    continue;
                }

                if ($this->propertyFetchAnalyzer->containsLocalPropertyFetchName($trait, $propertyName)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isSafeProtectedProperty(Property $property, Class_ $class): bool
    {
        if (! $property->isProtected()) {
            return false;
        }

        if (! $class->isFinal()) {
            return false;
        }

        return ! $class->extends instanceof FullyQualified;
    }
}

<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;

final class AllAssignNodePropertyTypeInferer
{
    public function __construct(
        private readonly AssignToPropertyTypeInferer $assignToPropertyTypeInferer,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function inferProperty(Property $property): ?Type
    {
        $classLike = $this->betterNodeFinder->findParentType($property, ClassLike::class);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        $propertyName = $this->nodeNameResolver->getName($property);
        return $this->assignToPropertyTypeInferer->inferPropertyInClassLike($propertyName, $classLike);
    }
}

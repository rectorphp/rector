<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;

final class AllAssignNodePropertyTypeInferer implements PropertyTypeInfererInterface
{
    public function __construct(
        private AssignToPropertyTypeInferer $assignToPropertyTypeInferer,
        private NodeNameResolver $nodeNameResolver,
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function inferProperty(Property $property): ?Type
    {
        $class = $this->betterNodeFinder->findParentType($property, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        $propertyName = $this->nodeNameResolver->getName($property);

        return $this->assignToPropertyTypeInferer->inferPropertyInClassLike($propertyName, $class);
    }

    public function getPriority(): int
    {
        return 1500;
    }
}

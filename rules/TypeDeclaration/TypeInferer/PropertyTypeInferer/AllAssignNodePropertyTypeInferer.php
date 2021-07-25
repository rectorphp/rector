<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;

final class AllAssignNodePropertyTypeInferer implements PropertyTypeInfererInterface
{
    public function __construct(
        private AssignToPropertyTypeInferer $assignToPropertyTypeInferer,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function inferProperty(Property $property): ?Type
    {
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            // anonymous class possibly?
            return null;
        }

        $propertyName = $this->nodeNameResolver->getName($property);

        return $this->assignToPropertyTypeInferer->inferPropertyInClassLike($propertyName, $classLike);
    }

    public function getPriority(): int
    {
        return 1500;
    }
}

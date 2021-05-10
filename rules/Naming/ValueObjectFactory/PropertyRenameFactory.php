<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObjectFactory;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Tests\Naming\ValueObjectFactory\PropertyRenameFactory\PropertyRenameFactoryTest
 */
final class PropertyRenameFactory
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function createFromExpectedName(Property $property, string $expectedName): ?PropertyRename
    {
        $currentName = $this->nodeNameResolver->getName($property);

        $propertyClassLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $propertyClassLike instanceof ClassLike) {
            return null;
        }

        $propertyClassLikeName = $property->getAttribute(AttributeKey::CLASS_NAME);
        if ($propertyClassLikeName === null) {
            return null;
        }

        return new PropertyRename(
            $property,
            $expectedName,
            $currentName,
            $propertyClassLike,
            $propertyClassLikeName,
            $property->props[0]
        );
    }
}

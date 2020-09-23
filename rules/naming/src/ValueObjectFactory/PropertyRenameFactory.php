<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObjectFactory;

use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Naming\ExpectedNameResolver\ExpectedNameResolverInterface;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Naming\Tests\ValueObjectFactory\PropertyRenameFactory\PropertyRenameFactoryTest
 */
final class PropertyRenameFactory
{
    /**
     * @var ExpectedNameResolverInterface
     */
    private $expectedNameResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function create(Property $property): ?PropertyRename
    {
        if (count($property->props) !== 1) {
            return null;
        }

        $expectedName = $this->expectedNameResolver->resolveIfNotYet($property);
        if ($expectedName === null) {
            return null;
        }

        $currentName = $this->nodeNameResolver->getName($property);

        $propertyClassLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($propertyClassLike === null) {
            throw new ShouldNotHappenException("There shouldn't be a property without Class Node");
        }

        return new PropertyRename($property, $expectedName, $currentName, $propertyClassLike, $property->props[0]);
    }

    public function setExpectedNameResolver(ExpectedNameResolverInterface $expectedNameResolver): void
    {
        $this->expectedNameResolver = $expectedNameResolver;
    }
}

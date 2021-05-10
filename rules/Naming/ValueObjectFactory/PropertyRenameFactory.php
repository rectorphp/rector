<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function createFromExpectedName(\PhpParser\Node\Stmt\Property $property, string $expectedName) : ?\Rector\Naming\ValueObject\PropertyRename
    {
        $currentName = $this->nodeNameResolver->getName($property);
        $propertyClassLike = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$propertyClassLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $propertyClassLikeName = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($propertyClassLikeName === null) {
            return null;
        }
        return new \Rector\Naming\ValueObject\PropertyRename($property, $expectedName, $currentName, $propertyClassLike, $propertyClassLikeName, $property->props[0]);
    }
}

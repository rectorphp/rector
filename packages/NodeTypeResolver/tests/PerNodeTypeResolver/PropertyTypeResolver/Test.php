<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver;

use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function testDocBlock(): void
    {
        $propertyNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/DocBlockDefinedProperty.php.inc', Property::class);

        $this->assertSame(
            ['SomeNamespace\PropertyType'],
            $propertyNodes[0]->getAttribute(Attribute::TYPES)
        );
    }

    public function testConstructorType(): void
    {
        $propertyNodes = $this->getNodesForFileOfType(
            __DIR__ . '/Source/ConstructorDefinedProperty.php.inc',
            Property::class
        );

        // @todo: add propertyFetch test
        $this->assertSame(
            ['SomeNamespace\PropertyType'],
            $propertyNodes[0]->getAttribute(Attribute::TYPES)
        );
    }
}

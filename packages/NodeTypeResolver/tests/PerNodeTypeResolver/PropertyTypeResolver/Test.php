<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver;

use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function testDocBlock(): void
    {
        $nodes = $this->getNodesWithTypesForFile(__DIR__ . '/Source/DocBlockDefinedProperty.inc');
        $propertyNodes = $this->nodeFinder->findInstanceOf($nodes, Property::class);

        $this->assertSame('SomeNamespace\PropertyType', $propertyNodes[0]->getAttribute(Attribute::TYPE));
    }

    public function testConstructorType(): void
    {
        $nodes = $this->getNodesWithTypesForFile(__DIR__ . '/Source/ConstructorDefinedProperty.php.inc');
        $propertyNodes = $this->nodeFinder->findInstanceOf($nodes, Property::class);

        $this->assertSame('SomeNamespace\PropertyType', $propertyNodes[0]->getAttribute(Attribute::TYPE));
    }
}

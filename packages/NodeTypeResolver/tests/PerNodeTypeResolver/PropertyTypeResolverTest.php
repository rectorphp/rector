<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver;

use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class PropertyTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function test(): void
    {
        $nodes = $this->getNodesWithTypesForFile(__DIR__ . '/Source/SomeClass.php.inc');
        $propertyNodes = $this->nodeFinder->findInstanceOf($nodes, Property::class);

        $this->assertSame('SomeNamespace\PropertyType', $propertyNodes[0]->getAttribute(Attribute::TYPE));
    }
}

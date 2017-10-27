<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver;

use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function testDocBlock(): void
    {
        $propertyFetchNodes = $this->getNodesForFileOfType(
            __DIR__ . '/Source/NestedProperty.php.inc',
            PropertyFetch::class
        );

        $this->assertCount(3, $propertyFetchNodes);

        $this->assertSame('name', $propertyFetchNodes[0]->name->toString());
        $this->assertSame(['PhpParser\NodeAbstract\Identifier'], $propertyFetchNodes[0]->getAttribute(Attribute::TYPES));

//        $this->assertSame('props', $propertyFetchNodes[1]->name->toString());
//        $this->assertSame(['PhpParser\Node\Stmt\PropertyProperty'], $propertyFetchNodes[1]->getAttribute(Attribute::TYPES));
//
//        $this->assertSame('node', $propertyFetchNodes[2]->name->toString());
//        $this->assertSame(['PhpParser\Node\Stmt\Property'], $propertyFetchNodes[2]->getAttribute(Attribute::TYPES));
    }
}

<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver;

use PhpParser\Node\Expr\PropertyFetch;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver
 */
final class PropertyFetchTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function testDocBlock(): void
    {
        $propertyFetchNodes = $this->getNodesForFileOfType(
            __DIR__ . '/Source/NestedProperty.php.inc',
            PropertyFetch::class
        );

        $this->assertCount(3, $propertyFetchNodes);

        $this->assertSame('name', $propertyFetchNodes[0]->name->toString());

        $this->assertSame(
            ['PhpParser\Node\VarLikeIdentifier'],
            $this->nodeTypeResolver->resolve($propertyFetchNodes[0])
        );

        $this->assertSame('props', $propertyFetchNodes[1]->name->toString());
        $this->assertSame(
            ['PhpParser\Node\Stmt\PropertyProperty'],
            $this->nodeTypeResolver->resolve($propertyFetchNodes[1])
        );

        $this->assertSame('node', $propertyFetchNodes[2]->name->toString());
        $this->assertSame([
            'PhpParser\Node\Stmt\Property',
            'PhpParser\Node\Stmt',
            'PhpParser\NodeAbstract',
        ], $this->nodeTypeResolver->resolve($propertyFetchNodes[2]));
    }
}

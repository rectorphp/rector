<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver;

use Iterator;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\Source\NestedProperty\ClassWithPropertyLevel1;
use Rector\NodeTypeResolver\Tests\Source\NestedProperty\ClassWithPropertyLevel2;
use Rector\NodeTypeResolver\Tests\Source\NestedProperty\ClassWithPropertyLevel3;
use Rector\NodeTypeResolver\Tests\Source\NestedProperty\ParentClass;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver
 */
final class PropertyFetchTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, string $propertyName, array $expectedTypes): void
    {
        /** @var PropertyFetch[] $propertyFetchNodes */
        $propertyFetchNodes = $this->getNodesForFileOfType($file, PropertyFetch::class);
        $propertyFetchNode = $propertyFetchNodes[$nodePosition];

        $this->assertSame($propertyName, (string) $propertyFetchNode->name);
        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($propertyFetchNode));
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/NestedProperty.php', 0, 'level3', [ClassWithPropertyLevel3::class]];
        yield [__DIR__ . '/Source/NestedProperty.php', 1, 'level2s', [ClassWithPropertyLevel2::class]];
        yield [
            __DIR__ . '/Source/NestedProperty.php',
            2,
            'level1',
            [ClassWithPropertyLevel1::class, ParentClass::class],
        ];
    }
}

<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Property;
use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Source\SomeChildOfValueObject;
use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Source\SomeValueObject;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\ClassThatExtendsHtml;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\Html;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\PropertyTypeResolver
 */
final class PropertyTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $propertyNodes = $this->getNodesForFileOfType($file, Property::class);
        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($propertyNodes[$nodePosition]));
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/ClassWithProperties.php', 0, [Html::class]];
        yield [__DIR__ . '/Source/ClassWithProperties.php', 1, [ClassThatExtendsHtml::class, Html::class]];

        // mimics failing test from DomainDrivenDesign set
        yield [__DIR__ . '/Source/fixture.php', 0, [SomeChildOfValueObject::class, SomeValueObject::class]];
    }
}

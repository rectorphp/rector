<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\ClassThatExtendsHtml;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\Html;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\SomeChild;
use Rector\PHPStan\TypeFactoryStaticHelper;

/**
 * @see \Rector\NodeTypeResolver\PerNodeTypeResolver\PropertyTypeResolver
 */
final class PropertyTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file, int $nodePosition, Type $expectedType): void
    {
        $propertyNodes = $this->getNodesForFileOfType($file, Property::class);

        $this->assertEquals($expectedType, $this->nodeTypeResolver->resolve($propertyNodes[$nodePosition]));
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/ClassWithProperties.php', 0, new ObjectType(Html::class)];

        yield [
            __DIR__ . '/Source/ClassWithProperties.php',
            1,
            TypeFactoryStaticHelper::createUnionObjectType([ClassThatExtendsHtml::class, Html::class]),
        ];

        // mimics failing test from DomainDrivenDesign set
        $unionType = TypeFactoryStaticHelper::createUnionObjectType([SomeChild::class, new NullType()]);
        yield [__DIR__ . '/Source/fixture.php', 0, $unionType];
    }
}

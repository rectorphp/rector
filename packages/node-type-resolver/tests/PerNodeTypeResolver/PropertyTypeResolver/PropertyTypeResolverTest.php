<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\ClassThatExtendsHtml;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\Html;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source\SomeChild;
use Rector\StaticTypeMapper\TypeFactory\TypeFactoryStaticHelper;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\PropertyTypeResolver
 */
final class PropertyTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file, int $nodePosition, Type $expectedType): void
    {
        $propertyNodes = $this->getNodesForFileOfType($file, Property::class);

        $resolvedType = $this->nodeTypeResolver->resolve($propertyNodes[$nodePosition]);

        // type is as expected
        $expectedTypeClass = get_class($expectedType);
        $this->assertInstanceOf($expectedTypeClass, $resolvedType);

        $expectedTypeAsString = $this->getStringFromType($expectedType);
        $resolvedTypeAsString = $this->getStringFromType($resolvedType);

        $this->assertEquals($expectedTypeAsString, $resolvedTypeAsString);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/MethodParamDocBlock.php', 0, new ObjectType(Html::class)];

        yield [
            __DIR__ . '/Source/MethodParamDocBlock.php',
            1,
            TypeFactoryStaticHelper::createUnionObjectType([ClassThatExtendsHtml::class, Html::class]),
        ];

        // mimics failing test from DomainDrivenDesign set
        $unionType = TypeFactoryStaticHelper::createUnionObjectType([SomeChild::class, new NullType()]);
        yield [__DIR__ . '/Source/ActionClass.php', 0, $unionType];
    }

    private function getStringFromType(Type $type): string
    {
        return $type->describe(VerbosityLevel::precise());
    }
}

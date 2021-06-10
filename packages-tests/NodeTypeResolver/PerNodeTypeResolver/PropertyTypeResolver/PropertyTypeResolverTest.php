<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyTypeResolver\Source\ClassThatExtendsHtml;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyTypeResolver\Source\Html;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyTypeResolver\Source\SomeChild;

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
        $expectedTypeClass = $expectedType::class;
        $this->assertInstanceOf($expectedTypeClass, $resolvedType);

        $expectedTypeAsString = $this->getStringFromType($expectedType);
        $resolvedTypeAsString = $this->getStringFromType($resolvedType);

        $this->assertEquals($expectedTypeAsString, $resolvedTypeAsString);
    }

    public function provideData(): Iterator
    {
        $unionTypeFactory = new UnionTypeFactory();

        yield [__DIR__ . '/Source/MethodParamDocBlock.php', 0, new ObjectType(Html::class)];

        yield [__DIR__ . '/Source/MethodParamDocBlock.php', 1, new ObjectType(ClassThatExtendsHtml::class)];

        // mimics failing test from DomainDrivenDesign set
        $unionType = $unionTypeFactory->createUnionObjectType([SomeChild::class, new NullType()]);
        yield [__DIR__ . '/Source/ActionClass.php', 0, $unionType];
    }

    private function getStringFromType(Type $type): string
    {
        return $type->describe(VerbosityLevel::precise());
    }
}

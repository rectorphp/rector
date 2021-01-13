<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\AnotherTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithParentClass;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithParentInterface;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithParentTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ParentClass;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\SomeInterface;
use Rector\StaticTypeMapper\TypeFactory\TypeFactoryStaticHelper;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\ClassAndInterfaceTypeResolver
 */
final class ClassTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider dataProvider()
     */
    public function test(string $file, int $nodePosition, Type $expectedType): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Class_::class);

        $resolvedType = $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]);
        $this->assertEquals($expectedType, $resolvedType);
    }

    public function dataProvider(): Iterator
    {
        yield [
            __DIR__ . '/Source/ClassWithParentInterface.php',
            0,
            TypeFactoryStaticHelper::createUnionObjectType([ClassWithParentInterface::class, SomeInterface::class]),
        ];

        yield [
            __DIR__ . '/Source/ClassWithParentClass.php',
            0,
            TypeFactoryStaticHelper::createUnionObjectType([ClassWithParentClass::class, ParentClass::class]),
        ];

        yield [
            __DIR__ . '/Source/ClassWithTrait.php',
            0,
            TypeFactoryStaticHelper::createUnionObjectType([ClassWithTrait::class, AnotherTrait::class]),
        ];

        yield [
            __DIR__ . '/Source/ClassWithParentTrait.php',
            0,
            TypeFactoryStaticHelper::createUnionObjectType(
                [ClassWithParentTrait::class, ClassWithTrait::class, AnotherTrait::class]
            ),
        ];

        yield [
            __DIR__ . '/Source/AnonymousClass.php',
            0,
            TypeFactoryStaticHelper::createUnionObjectType(
                [
                    'AnonymousClassdefa360846b84894d4be1b25c2ce6da9',
                    ParentClass::class,
                    SomeInterface::class,
                    AnotherTrait::class,
                ]
            ),
        ];
    }
}

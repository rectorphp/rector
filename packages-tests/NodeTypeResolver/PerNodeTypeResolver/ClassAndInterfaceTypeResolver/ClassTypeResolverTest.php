<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithParentClass;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithParentInterface;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithParentTrait;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithTrait;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\ClassAndInterfaceTypeResolver
 */
final class ClassTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider dataProvider()
     */
    public function test(string $file, int $nodePosition, ObjectType $expectedObjectType): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Class_::class);

        $resolvedType = $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]);
        $this->assertInstanceOf(TypeWithClassName::class, $resolvedType);

        /** @var TypeWithClassName $resolvedType */
        $this->assertEquals($expectedObjectType->getClassName(), $resolvedType->getClassName());
    }

    public function dataProvider(): Iterator
    {
        yield [
            __DIR__ . '/Source/ClassWithParentInterface.php',
            0,
            new ObjectType(ClassWithParentInterface::class),
        ];

        yield [__DIR__ . '/Source/ClassWithParentClass.php', 0, new ObjectType(ClassWithParentClass::class)];

        yield [__DIR__ . '/Source/ClassWithTrait.php', 0, new ObjectType(ClassWithTrait::class)];

        yield [__DIR__ . '/Source/ClassWithParentTrait.php', 0, new ObjectType(ClassWithParentTrait::class)];
    }
    
    public function testAnonymousClass(): void
    {
        $file = __DIR__ . '/Source/AnonymousClass.php';
        $nodePosition = 0;
        
        $variableNodes = $this->getNodesForFileOfType($file, Class_::class);

        $resolvedType = $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]);
        $this->assertInstanceOf(TypeWithClassName::class, $resolvedType);

        /** @var TypeWithClassName $resolvedType */
        // anonymous classes contain a hash, which is different between platforms
        $this->assertStringStartsWith('AnonymousClass', $resolvedType->getClassName());
    }
}

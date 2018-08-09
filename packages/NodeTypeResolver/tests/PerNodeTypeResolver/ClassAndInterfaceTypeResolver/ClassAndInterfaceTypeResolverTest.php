<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\AnotherTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithParentClass;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithParentInterface;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithParentTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ClassWithTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\ParentClass;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\SomeInterface;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver
 */
final class ClassAndInterfaceTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider dataProvider()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Class_::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    public function dataProvider(): Iterator
    {
//        yield [__DIR__ . '/Source/ClassWithParentInterface.php', 0, [
//            ClassWithParentInterface::class,
//            SomeInterface::class,
//        ]];
//
//        yield [__DIR__ . '/Source/ClassWithParentClass.php', 0, [
//            ClassWithParentClass::class,
//            ParentClass::class,
//        ]];

//        yield [__DIR__ . '/Source/ClassWithTrait.php', 0, [ClassWithTrait::class, AnotherTrait::class]];

        yield [__DIR__ . '/Source/ClassWithParentTrait.php', 0, [ClassWithParentTrait::class, ClassWithTrait::class, AnotherTrait::class]];

//        yield [__DIR__ . '/Source/AnonymousClass.php', 0, [ParentClass::class, SomeInterface::class]];
    }
}

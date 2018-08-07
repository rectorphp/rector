<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver\Source\AnotherTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver\Source\ClassWithParentClass;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver\Source\ClassWithParentInterface;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver\Source\ClassWithTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver\Source\ParentClass;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver\Source\SomeInterface;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\ClassTypeResolver
 */
final class ClassTypeResolverTest extends AbstractNodeTypeResolverTest
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
        yield [__DIR__ . '/Source/ClassWithParentInterface.php', 0, [
            ClassWithParentInterface::class,
            SomeInterface::class,
        ]];

        yield [__DIR__ . '/Source/ClassWithParentClass.php', 0, [
            ClassWithParentClass::class,
            ParentClass::class,
        ]];

        yield [__DIR__ . '/Source/ClassWithTrait.php', 0, [ClassWithTrait::class, AnotherTrait::class]];

        // traits in anonymous classes are ignored in PHPStan
        yield [
            __DIR__ . '/Source/AnonymousClass.php',
            0,
            [ParentClass::class, SomeInterface::class],
        ];
    }
}

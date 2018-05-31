<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver;

use Iterator;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver\Source\AnotherTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver\Source\ClassWithParentInterface;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassTypeResolver\Source\ClassWithTrait;
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
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    public function dataProvider(): Iterator
    {
        # assign of "new <name>"
        yield [__DIR__ . '/Source/ClassWithParentInterface.php', 0, [
            ClassWithParentInterface::class,
            SomeInterface::class,
        ]];
        yield [__DIR__ . '/Source/ClassWithTrait.php', 0, [ClassWithTrait::class, AnotherTrait::class]];
    }
}

<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\SomeInterfaceWithParentInterface;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\SomeParentInterface;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver
 */
final class InterfaceTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider dataProvider()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Interface_::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    public function dataProvider(): Iterator
    {
        yield [
            __DIR__ . '/Source/SomeInterfaceWithParentInterface.php',
            0,
            [SomeInterfaceWithParentInterface::class, SomeParentInterface::class],
        ];
    }
}

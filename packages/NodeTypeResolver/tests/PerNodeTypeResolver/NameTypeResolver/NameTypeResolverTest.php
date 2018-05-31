<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\NameTypeResolver;

use Iterator;
use PhpParser\Node\Name;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\Source\AnotherClass;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\NameTypeResolver
 */
final class NameTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $nameNodes = $this->getNodesForFileOfType($file, Name::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($nameNodes[$nodePosition]));
    }

    public function provideData(): Iterator
    {
        # test new
        yield [__DIR__ . '/Source/ParentCall.php', 2, [AnotherClass::class]];
    }
}

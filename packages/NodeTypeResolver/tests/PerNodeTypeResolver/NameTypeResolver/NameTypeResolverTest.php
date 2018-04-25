<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\NameTypeResolver;

use Iterator;
use PhpParser\Node\Name;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\NameTypeResolver
 */
final class NameTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideTypeForNodesAndFilesData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $nameNodes = $this->getNodesForFileOfType($file, Name::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($nameNodes[$nodePosition]));
    }

    public function provideTypeForNodesAndFilesData(): Iterator
    {
        # test new
        yield [__DIR__ . '/Source/ParentCall.php.inc', 2, ['Rector\NodeTypeResolver\Tests\Source\AnotherClass']];
    }
}

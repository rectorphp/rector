<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\NameTypeResolver;

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
        if (PHP_VERSION >= '7.2.0') {
            $this->markTestSkipped('This test needs PHP 7.1 or lower.');
        }

        $nameNodes = $this->getNodesForFileOfType($file, Name::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($nameNodes[$nodePosition]));
    }

    /**
     * @return mixed[][]
     */
    public function provideTypeForNodesAndFilesData(): array
    {
        return [
            # test new
            [__DIR__ . '/Source/ParentCall.php.inc', 2, ['Nette\Config\Configurator']],
        ];
    }
}

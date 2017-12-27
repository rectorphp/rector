<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver
 */
final class ParamTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideTypeForNodesAndFilesData()
     * @param string[]
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    /**
     * @return mixed[][]
     */
    public function provideTypeForNodesAndFilesData(): array
    {
        return [
            # typehint
            [__DIR__ . '/Source/MethodParamTypeHint.php.inc', 0, ['SomeNamespace\SubNamespace\Html']],
            # docblock
            [__DIR__ . '/Source/MethodParamDocBlock.php.inc', 0, ['SomeNamespace\SubNamespace\Html']],
        ];
    }
}

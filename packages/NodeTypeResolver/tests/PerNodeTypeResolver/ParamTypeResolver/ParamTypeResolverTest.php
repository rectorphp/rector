<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver;

use Iterator;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver\Source\Html;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver
 */
final class ParamTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/MethodParamTypeHint.php', 0, [Html::class]];
        yield [__DIR__ . '/Source/MethodParamDocBlock.php', 0, [Html::class]];
    }
}

<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver
 */
final class VariableTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideTypeForNodesAndFilesData()
     * @param string[]
     */
    public function testCallbackArgumentTypehint(string $file, int $position, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$position]));
    }

    /**
     * @return mixed[][]
     */
    public function provideTypeForNodesAndFilesData(): array
    {
        return [
            # this
            [__DIR__ . '/Source/This.php.inc', 0, ['SomeNamespace\SomeClass', 'SomeNamespace\AnotherClass']],
            # new
            [__DIR__ . '/Source/SomeClass.php.inc', 0, ['SomeNamespace\AnotherType']],
            [__DIR__ . '/Source/SomeClass.php.inc', 2, ['SomeNamespace\AnotherType']],
            # assignment
            [__DIR__ . '/Source/SomeClass.php.inc', 1, ['SomeNamespace\AnotherType']],
            # callback arguments
            [__DIR__ . '/Source/ArgumentTypehint.php.inc', 0, ['SomeNamespace\UseUse']],
            [__DIR__ . '/Source/ArgumentTypehint.php.inc', 1, ['SomeNamespace\UseUse']],
        ];
    }
}

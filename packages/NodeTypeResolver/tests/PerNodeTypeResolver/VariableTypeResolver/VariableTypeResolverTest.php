<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver;

use Iterator;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\Source\AnotherClass;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver
 */
final class VariableTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideTypeForNodesAndFilesData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    public function provideTypeForNodesAndFilesData(): Iterator
    {
        # this
        yield [__DIR__ . '/Source/This.php.inc', 0, ['SomeNamespace\ThisClass', AnotherClass::class]];
        # new
        yield [__DIR__ . '/Source/SomeClass.php.inc', 0, ['SomeNamespace\AnotherType']];
        yield [__DIR__ . '/Source/SomeClass.php.inc', 2, ['SomeNamespace\AnotherType']];
        # assignment
        yield [__DIR__ . '/Source/SomeClass.php.inc', 1, ['SomeNamespace\AnotherType']];
        # callback arguments
        yield [__DIR__ . '/Source/ArgumentTypehint.php.inc', 0, ['SomeNamespace\UseUse']];
        yield [__DIR__ . '/Source/ArgumentTypehint.php.inc', 1, ['SomeNamespace\UseUse']];
    }
}

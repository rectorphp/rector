<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver;

use Iterator;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source\AnotherType;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source\ThisClass;
use Rector\NodeTypeResolver\Tests\Source\AnotherClass;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\VariableTypeResolver
 */
final class VariableTypeResolverTest extends AbstractNodeTypeResolverTest
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
        yield [__DIR__ . '/Source/ThisClass.php', 0, [ThisClass::class, AnotherClass::class]];

        yield [__DIR__ . '/Source/NewClass.php', 0, [AnotherType::class]];
        yield [__DIR__ . '/Source/NewClass.php', 2, [AnotherType::class]];

        yield [__DIR__ . '/Source/AssignmentClass.php', 0, [AnotherType::class]];
        yield [__DIR__ . '/Source/AssignmentClass.php', 1, [AnotherType::class]];

        yield [__DIR__ . '/Source/ArgumentTypehint.php', 0, [AnotherType::class]];
        yield [__DIR__ . '/Source/ArgumentTypehint.php', 1, [AnotherType::class]];
    }
}

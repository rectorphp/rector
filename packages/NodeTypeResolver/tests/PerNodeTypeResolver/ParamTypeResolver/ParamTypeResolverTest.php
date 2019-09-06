<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver;

use Iterator;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver\Source\Html;

/**
 * @see \Rector\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver
 */
final class ParamTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file, int $nodePosition, Type $expectedType): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $this->assertEquals($expectedType, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    public function provideData(): Iterator
    {
        $objectType = new ObjectType(Html::class);

        yield [__DIR__ . '/Source/MethodParamTypeHint.php', 0, $objectType];
        yield [__DIR__ . '/Source/MethodParamDocBlock.php', 0, $objectType];
    }
}

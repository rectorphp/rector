<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver;

use Iterator;
use PhpParser\Node\Param;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver\Source\Html;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\ParamTypeResolver
 */
final class ParamTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file, int $nodePosition, Type $expectedType): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Param::class);

        $resolvedType = $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]);
        $this->assertEquals($expectedType, $resolvedType);
    }

    public function provideData(): Iterator
    {
        $objectType = new ObjectType(Html::class);

        yield [__DIR__ . '/Source/MethodParamTypeHint.php', 0, $objectType];
        yield [__DIR__ . '/Source/MethodParamDocBlock.php', 0, $objectType];
    }
}

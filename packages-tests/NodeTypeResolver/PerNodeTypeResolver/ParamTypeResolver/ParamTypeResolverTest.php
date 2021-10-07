<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver;

use Iterator;
use PhpParser\Node\Param;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver\Source\Html;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\ParamTypeResolver
 */
final class ParamTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file, int $nodePosition, TypeWithClassName $expectedTypeWithClassName): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Param::class);

        $resolvedType = $this->nodeTypeResolver->getType($variableNodes[$nodePosition]);

        $this->assertInstanceOf(TypeWithClassName::class, $resolvedType);

        /** @var TypeWithClassName $resolvedType */
        $this->assertSame($expectedTypeWithClassName->getClassName(), $resolvedType->getClassName());
    }

    public function provideData(): Iterator
    {
        $objectType = new ObjectType(Html::class);

        yield [__DIR__ . '/Source/MethodParamTypeHint.php', 0, $objectType];
        yield [__DIR__ . '/Source/MethodParamDocBlock.php', 0, $objectType];
    }
}

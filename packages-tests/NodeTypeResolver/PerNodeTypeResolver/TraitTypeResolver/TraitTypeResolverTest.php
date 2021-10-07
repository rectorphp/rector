<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\TraitTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\TraitTypeResolver\Source\AnotherTrait;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\TraitTypeResolver\Source\TraitWithTrait;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\TraitTypeResolver
 */
final class TraitTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file, int $nodePosition, Type $expectedType): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Trait_::class);

        $resolvedType = $this->nodeTypeResolver->getType($variableNodes[$nodePosition]);
        $this->assertEquals($expectedType, $resolvedType);
    }

    /**
     * @return Iterator<int[]|string[]|UnionType[]>
     */
    public function provideData(): Iterator
    {
        $unionTypeFactory = new UnionTypeFactory();

        yield [
            __DIR__ . '/Source/TraitWithTrait.php',
            0,
            $unionTypeFactory->createUnionObjectType([AnotherTrait::class, TraitWithTrait::class]),
        ];
    }
}

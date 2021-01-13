<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\TraitTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\TraitTypeResolver\Source\AnotherTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\TraitTypeResolver\Source\TraitWithTrait;
use Rector\StaticTypeMapper\TypeFactory\TypeFactoryStaticHelper;

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

        $resolvedType = $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]);
        $this->assertEquals($expectedType, $resolvedType);
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Source/TraitWithTrait.php',
            0,
            TypeFactoryStaticHelper::createUnionObjectType([AnotherTrait::class, TraitWithTrait::class]),
        ];
    }
}

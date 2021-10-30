<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\TraitTypeResolver;

use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\TraitTypeResolver\Source\AnotherTrait;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\TraitTypeResolver\Source\TraitWithTrait;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\TraitTypeResolver
 */
final class TraitTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function test(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/TraitWithTrait.php', Trait_::class);

        $resolvedType = $this->nodeTypeResolver->getType($variableNodes[0]);
        $expectedUnionType = $this->createExpectedType();

        $this->assertEquals($expectedUnionType, $resolvedType);
    }

    private function createExpectedType(): UnionType
    {
        $anotherTraitObjectType = new ObjectType(AnotherTrait::class);
        $traitWithTraitObjectType = new ObjectType(TraitWithTrait::class);

        return new UnionType([$anotherTraitObjectType, $traitWithTraitObjectType]);
    }
}

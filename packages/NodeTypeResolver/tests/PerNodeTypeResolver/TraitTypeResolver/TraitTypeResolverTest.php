<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\TraitTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\TraitTypeResolver\Source\AnotherTrait;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\TraitTypeResolver\Source\TraitWithTrait;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\ClassLikeTypeResolver
 */
final class TraitTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Trait_::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/TraitWithTrait.php', 0, [TraitWithTrait::class, AnotherTrait::class]];
    }
}

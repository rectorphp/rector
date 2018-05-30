<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class TraitTypeResolver extends AbstractClassLikeTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Trait_::class];
    }

    /**
     * @param Trait_ $traitNode
     * @return string[]
     */
    public function resolve(Node $traitNode): array
    {
        $types[] = $this->resolveNameNode($traitNode);
        $types = array_merge($types, $this->resolveUsedTraitTypes($traitNode));

        return $types;
    }
}

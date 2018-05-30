<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeAnalyzer\ClassLikeAnalyzer;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class TraitTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var ClassLikeAnalyzer
     */
    private $classLikeAnalyzer;

    public function __construct(ClassLikeAnalyzer $classLikeAnalyzer)
    {
        $this->classLikeAnalyzer = $classLikeAnalyzer;
    }

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
        $types[] = $this->classLikeAnalyzer->resolveNameNode($traitNode);
        $types = array_merge($types, $this->classLikeAnalyzer->resolveUsedTraitTypes($traitNode));

        return $types;
    }
}

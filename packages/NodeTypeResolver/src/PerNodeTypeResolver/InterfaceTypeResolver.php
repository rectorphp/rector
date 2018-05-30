<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeAnalyzer\ClassLikeAnalyzer;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class InterfaceTypeResolver implements PerNodeTypeResolverInterface
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
        return [Interface_::class];
    }

    /**
     * @param Interface_ $interfaceNode
     * @return string[]
     */
    public function resolve(Node $interfaceNode): array
    {
        $className = $this->classLikeAnalyzer->resolveNameNode($interfaceNode);

        return array_merge([$className], $this->classLikeAnalyzer->resolveExtendsTypes($interfaceNode, $className));
    }
}

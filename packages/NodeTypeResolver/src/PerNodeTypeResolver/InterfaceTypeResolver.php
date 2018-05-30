<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class InterfaceTypeResolver extends AbstractClassLikeTypeResolver implements PerNodeTypeResolverInterface
{
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
        $className = $this->resolveNameNode($interfaceNode);

        return array_merge([$className], $this->resolveExtendsTypes($interfaceNode, $className));
    }
}

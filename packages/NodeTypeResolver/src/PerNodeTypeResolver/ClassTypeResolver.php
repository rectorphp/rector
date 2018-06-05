<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class ClassTypeResolver extends AbstractClassLikeTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $classNode
     * @return string[]
     */
    public function resolve(Node $classNode): array
    {
        $className = $this->resolveNameNode($classNode);

        $types = [];
        if ($className) {
            $types[] = $className;
        }

        $types = array_merge($types, $this->resolveExtendsTypes($classNode, $className));
        $types = array_merge($types, $this->resolveImplementsTypes($classNode));
        return array_merge($types, $this->resolveUsedTraitTypes($classNode));
    }
}

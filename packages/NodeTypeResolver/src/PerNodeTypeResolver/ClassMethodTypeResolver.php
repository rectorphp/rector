<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class ClassMethodTypeResolver implements PerNodeTypeResolverInterface
{
    public function getNodeClass(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $classMethodNode
     * @return string[]
     */
    public function resolve(Node $classMethodNode): array
    {
        /** @var ClassLike $classLikeNode */
        $classLikeNode = $classMethodNode->getAttribute(Attribute::CLASS_NODE);

        return (array) $classLikeNode->getAttribute(Attribute::TYPES);
    }
}

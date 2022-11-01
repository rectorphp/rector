<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix202211\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements NodeNameResolverInterface<ClassLike>
 */
final class ClassNameResolver implements NodeNameResolverInterface
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @required
     */
    public function autowire(NodeNameResolver $nodeNameResolver) : void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function getNode() : string
    {
        return ClassLike::class;
    }
    /**
     * @param ClassLike $node
     */
    public function resolve(Node $node) : ?string
    {
        if ($node->namespacedName instanceof Name) {
            return $node->namespacedName->toString();
        }
        if ($node->name === null) {
            return null;
        }
        return $this->nodeNameResolver->getName($node->name);
    }
}

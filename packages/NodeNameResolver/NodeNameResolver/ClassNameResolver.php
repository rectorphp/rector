<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20211020\Symfony\Contracts\Service\Attribute\Required;
final class ClassNameResolver implements \Rector\NodeNameResolver\Contract\NodeNameResolverInterface
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @required
     */
    public function autowireClassNameResolver(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver) : void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return class-string<Node>
     */
    public function getNode() : string
    {
        return \PhpParser\Node\Stmt\ClassLike::class;
    }
    /**
     * @param Class_ $node
     */
    public function resolve(\PhpParser\Node $node) : ?string
    {
        if (\property_exists($node, 'namespacedName')) {
            return $node->namespacedName->toString();
        }
        if ($node->name === null) {
            return null;
        }
        return $this->nodeNameResolver->getName($node->name);
    }
}

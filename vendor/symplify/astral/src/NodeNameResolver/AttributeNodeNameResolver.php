<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\Astral\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use RectorPrefix20210827\Symplify\Astral\Contract\NodeNameResolverInterface;
final class AttributeNodeNameResolver implements \RectorPrefix20210827\Symplify\Astral\Contract\NodeNameResolverInterface
{
    /**
     * @param \PhpParser\Node $node
     */
    public function match($node) : bool
    {
        return $node instanceof \PhpParser\Node\Attribute;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : ?string
    {
        return $node->name->toString();
    }
}

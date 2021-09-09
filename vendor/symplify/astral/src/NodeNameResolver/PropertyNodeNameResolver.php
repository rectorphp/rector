<?php

declare (strict_types=1);
namespace RectorPrefix20210909\Symplify\Astral\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use RectorPrefix20210909\Symplify\Astral\Contract\NodeNameResolverInterface;
final class PropertyNodeNameResolver implements \RectorPrefix20210909\Symplify\Astral\Contract\NodeNameResolverInterface
{
    /**
     * @param \PhpParser\Node $node
     */
    public function match($node) : bool
    {
        return $node instanceof \PhpParser\Node\Stmt\Property;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : ?string
    {
        $propertyProperty = $node->props[0];
        return (string) $propertyProperty->name;
    }
}

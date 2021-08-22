<?php

declare (strict_types=1);
namespace RectorPrefix20210822\Symplify\Astral\Contract;

use PhpParser\Node;
interface NodeNameResolverInterface
{
    /**
     * @param \RectorPrefix20210822\PhpParser\Node $node
     */
    public function match($node) : bool;
    /**
     * @param \RectorPrefix20210822\PhpParser\Node $node
     */
    public function resolve($node) : ?string;
}

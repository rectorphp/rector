<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Symplify\Astral\Contract;

use RectorPrefix20220606\PhpParser\Node;
interface NodeNameResolverInterface
{
    public function match(Node $node) : bool;
    public function resolve(Node $node) : ?string;
}

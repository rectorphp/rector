<?php

declare (strict_types=1);
namespace RectorPrefix20210522\Symplify\Astral\Contract;

use PhpParser\Node;
interface NodeNameResolverInterface
{
    public function match(\PhpParser\Node $node) : bool;
    public function resolve(\PhpParser\Node $node) : ?string;
}

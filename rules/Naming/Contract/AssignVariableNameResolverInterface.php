<?php

declare (strict_types=1);
namespace Rector\Naming\Contract;

use PhpParser\Node;
/**
 * @template TNode as Node
 */
interface AssignVariableNameResolverInterface
{
    /**
     * @param \PhpParser\Node $node
     */
    public function match($node) : bool;
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : string;
}

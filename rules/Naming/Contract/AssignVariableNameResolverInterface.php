<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Contract;

use RectorPrefix20220606\PhpParser\Node;
/**
 * @template TNode as Node
 */
interface AssignVariableNameResolverInterface
{
    public function match(Node $node) : bool;
    /**
     * @param TNode $node
     */
    public function resolve(Node $node) : string;
}

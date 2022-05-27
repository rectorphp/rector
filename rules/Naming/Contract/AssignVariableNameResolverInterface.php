<?php

declare (strict_types=1);
namespace Rector\Naming\Contract;

use PhpParser\Node;
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

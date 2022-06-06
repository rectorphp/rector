<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Contract\Rector;

use RectorPrefix20220606\Nette\Neon\Node;
use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
/**
 * @template TNode as Node
 */
interface NeonRectorInterface extends RectorInterface
{
    /**
     * @return class-string<TNode>
     */
    public function getNodeType() : string;
    /**
     * @param TNode $node
     * @return \Nette\Neon\Node|null
     */
    public function enterNode(Node $node);
}

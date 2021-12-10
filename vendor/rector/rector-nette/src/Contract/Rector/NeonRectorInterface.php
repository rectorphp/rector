<?php

declare (strict_types=1);
namespace Rector\Nette\Contract\Rector;

use RectorPrefix20211210\Nette\Neon\Node;
use Rector\Core\Contract\Rector\RectorInterface;
/**
 * @template TNode as Node
 */
interface NeonRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * @return class-string<TNode>
     */
    public function getNodeType() : string;
    /**
     * @param \Nette\Neon\Node $node
     * @return \Nette\Neon\Node|null
     */
    public function enterNode($node);
}

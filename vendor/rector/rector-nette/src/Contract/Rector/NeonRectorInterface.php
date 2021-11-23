<?php

declare (strict_types=1);
namespace Rector\Nette\Contract\Rector;

use RectorPrefix20211123\Nette\Neon\Node;
use Rector\Core\Contract\Rector\RectorInterface;
interface NeonRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * @return class-string<Node>
     */
    public function getNodeType() : string;
    /**
     * @return \Nette\Neon\Node|null
     */
    public function enterNode(\RectorPrefix20211123\Nette\Neon\Node $node);
}

<?php

namespace Rector\Nette\NeonParser\Contract;

use RectorPrefix20211123\Nette\Neon\Node;
interface NeonNodeVisitorInterface
{
    /**
     * @return class-string<\PhpParser\Node>
     */
    public function getNodeType() : string;
    /**
     * @param \Nette\Neon\Node $node
     */
    public function enterNode($node) : \RectorPrefix20211123\Nette\Neon\Node;
}

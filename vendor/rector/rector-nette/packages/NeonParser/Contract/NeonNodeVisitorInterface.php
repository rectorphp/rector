<?php

namespace RectorPrefix20220606\Rector\Nette\NeonParser\Contract;

use RectorPrefix20220606\Nette\Neon\Node;
interface NeonNodeVisitorInterface
{
    /**
     * @return class-string<\PhpParser\Node>
     */
    public function getNodeType() : string;
    public function enterNode(Node $node) : Node;
}

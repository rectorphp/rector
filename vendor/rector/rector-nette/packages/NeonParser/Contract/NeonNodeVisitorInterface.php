<?php

namespace Rector\Nette\NeonParser\Contract;

use RectorPrefix20220216\Nette\Neon\Node;
interface NeonNodeVisitorInterface
{
    /**
     * @return class-string<\PhpParser\Node>
     */
    public function getNodeType() : string;
    public function enterNode(\RectorPrefix20220216\Nette\Neon\Node $node) : \RectorPrefix20220216\Nette\Neon\Node;
}

<?php

namespace Rector\Nette\NeonParser\Contract;

use RectorPrefix20211214\Nette\Neon\Node;
interface NeonNodeVisitorInterface
{
    /**
     * @return class-string<\PhpParser\Node>
     */
    public function getNodeType() : string;
    public function enterNode(\RectorPrefix20211214\Nette\Neon\Node $node) : \RectorPrefix20211214\Nette\Neon\Node;
}

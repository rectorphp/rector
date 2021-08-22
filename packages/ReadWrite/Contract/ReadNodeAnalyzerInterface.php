<?php

declare (strict_types=1);
namespace Rector\ReadWrite\Contract;

use PhpParser\Node;
interface ReadNodeAnalyzerInterface
{
    /**
     * @param \RectorPrefix20210822\PhpParser\Node $node
     */
    public function supports($node) : bool;
    /**
     * @param \RectorPrefix20210822\PhpParser\Node $node
     */
    public function isRead($node) : bool;
}

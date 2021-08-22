<?php

declare (strict_types=1);
namespace Rector\VendorLocker\Contract;

use PhpParser\Node;
interface NodeVendorLockerInterface
{
    /**
     * @param \RectorPrefix20210822\PhpParser\Node $node
     */
    public function resolve($node) : bool;
}

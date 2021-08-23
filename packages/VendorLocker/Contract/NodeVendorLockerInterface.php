<?php

declare (strict_types=1);
namespace Rector\VendorLocker\Contract;

use PhpParser\Node;
interface NodeVendorLockerInterface
{
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : bool;
}

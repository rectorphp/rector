<?php

declare (strict_types=1);
namespace Rector\Nette\NeonParser\Node;

use RectorPrefix202208\Nette\Neon\Node;
use Rector\Nette\NeonParser\Exception\UnusedVirtualMethodException;
abstract class AbstractVirtualNode extends Node
{
    /**
     * @return mixed
     */
    public function toValue()
    {
        // never used, just to make parent contract happy
        throw new UnusedVirtualMethodException();
    }
    public function toString() : string
    {
        // never used, just to make parent contract happy
        throw new UnusedVirtualMethodException();
    }
}

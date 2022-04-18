<?php

declare (strict_types=1);
namespace Rector\Nette\NeonParser\Node;

use RectorPrefix20220418\Nette\Neon\Node;
use Rector\Nette\NeonParser\Exception\UnusedVirtualMethodException;
abstract class AbstractVirtualNode extends \RectorPrefix20220418\Nette\Neon\Node
{
    /**
     * @return mixed
     */
    public function toValue()
    {
        // never used, just to make parent contract happy
        throw new \Rector\Nette\NeonParser\Exception\UnusedVirtualMethodException();
    }
    public function toString() : string
    {
        // never used, just to make parent contract happy
        throw new \Rector\Nette\NeonParser\Exception\UnusedVirtualMethodException();
    }
}

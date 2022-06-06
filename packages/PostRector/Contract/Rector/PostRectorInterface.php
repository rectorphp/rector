<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PostRector\Contract\Rector;

use RectorPrefix20220606\PhpParser\NodeVisitor;
use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
interface PostRectorInterface extends NodeVisitor, RectorInterface
{
    /**
     * Higher values are executed first
     */
    public function getPriority() : int;
}

<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PostRector\Rector;

use RectorPrefix20220606\PhpParser\NodeVisitorAbstract;
use RectorPrefix20220606\Rector\PostRector\Contract\Rector\PostRectorInterface;
abstract class AbstractPostRector extends NodeVisitorAbstract implements PostRectorInterface
{
}

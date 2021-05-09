<?php

declare (strict_types=1);
namespace Rector\PostRector\Contract\Rector;

use PhpParser\NodeVisitor;
use Rector\Core\Contract\Rector\RectorInterface;
interface PostRectorInterface extends \PhpParser\NodeVisitor, \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * Higher values are executed first
     */
    public function getPriority() : int;
}

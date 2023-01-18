<?php

declare (strict_types=1);
namespace Rector\PostRector\Contract\Rector;

use Rector\Core\Contract\Rector\RectorInterface;
interface PostRectorDependencyInterface
{
    /**
     * @return class-string<RectorInterface>[]
     */
    public function getRectorDependencies() : array;
}

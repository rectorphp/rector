<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PostRector\Contract\Collector;

interface NodeCollectorInterface
{
    public function isActive() : bool;
}

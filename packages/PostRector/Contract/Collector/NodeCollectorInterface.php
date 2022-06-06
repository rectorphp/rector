<?php

declare (strict_types=1);
namespace Rector\PostRector\Contract\Collector;

interface NodeCollectorInterface
{
    public function isActive() : bool;
}

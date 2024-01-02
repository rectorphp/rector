<?php

declare (strict_types=1);
namespace Rector\Contract\DependencyInjection;

interface ResetableInterface
{
    public function reset() : void;
}

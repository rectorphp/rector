<?php

declare (strict_types=1);
namespace Rector\Core\Contract\DependencyInjection;

interface ResetableInterface
{
    public function reset() : void;
}

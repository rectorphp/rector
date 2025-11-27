<?php

declare (strict_types=1);
namespace Rector\Contract\DependencyInjection;

interface ResettableInterface
{
    public function reset(): void;
}

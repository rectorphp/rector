<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source;

final class Cell
{
    public function setName($name): self
    {
        return $this;
    }
}

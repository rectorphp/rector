<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source;

final class Row
{
    public function addCell(): Cell
    {
        return new Cell();
    }
}

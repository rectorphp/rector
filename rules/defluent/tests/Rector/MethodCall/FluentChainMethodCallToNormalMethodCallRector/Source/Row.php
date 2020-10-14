<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source;

final class Row
{
    public function addCell(): Cell
    {
        return new Cell();
    }
}

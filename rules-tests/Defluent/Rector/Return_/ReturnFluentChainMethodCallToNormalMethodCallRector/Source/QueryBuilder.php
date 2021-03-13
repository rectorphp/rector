<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source;

final class QueryBuilder
{
    public function addQuery(): self
    {
        return $this;
    }

    public function select(): self
    {
        return $this;
    }
}

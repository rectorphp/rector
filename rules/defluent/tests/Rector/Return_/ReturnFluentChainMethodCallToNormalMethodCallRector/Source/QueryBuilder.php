<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source;

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

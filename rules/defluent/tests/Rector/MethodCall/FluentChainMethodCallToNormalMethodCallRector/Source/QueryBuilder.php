<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source;

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

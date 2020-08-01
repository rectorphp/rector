<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source;

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

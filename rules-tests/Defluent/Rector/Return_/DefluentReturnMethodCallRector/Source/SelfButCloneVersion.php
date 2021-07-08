<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source;

final class SelfButCloneVersion
{
    public function duplicate(): self
    {
        return clone $this;
    }
}

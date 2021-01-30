<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\Return_\DefluentReturnMethodCallRector\Source;

final class SelfButNewVersion
{
    public function duplicate(): self
    {
        return new self();
    }
}

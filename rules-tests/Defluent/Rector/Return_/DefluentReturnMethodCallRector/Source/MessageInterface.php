<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source;

interface MessageInterface
{
    public function withStatus($status): self;
}

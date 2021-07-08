<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source;

final class SelfStaticThisButNewVersion
{
    public function duplicateSelf(): self
    {
        return new self();
    }

    public function duplicateStatic(): self
    {
        return new static();
    }

    public function duplicateThis(): self
    {
        return new $this;
    }
}

<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source;

final class DifferentReturnValues implements FluentInterfaceClassInterface
{
    public function someFunction(): self
    {
        return $this;
    }

    public function otherFunction(): int
    {
        return 5;
    }
}

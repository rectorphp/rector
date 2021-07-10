<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source;

final class FluentInterfaceClass extends InterFluentInterfaceClass
{
    /**
     * @var int
     */
    private $value = 0;

    public function someFunction(): self
    {
        return $this;
    }

    public function otherFunction(): self
    {
        return $this;
    }

    public function paginate(): array
    {
        return [];
    }

    public function voidReturningMethod()
    {
        $this->value = 100;
    }
}

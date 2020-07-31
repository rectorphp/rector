<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source;

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

    public function voidReturningMethod()
    {
        $this->value = 100;
    }
}

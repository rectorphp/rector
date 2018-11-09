<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Wrong;

class AnyClass
{
    public function reset()
    {
        return $this->getValue() !== null;
    }

    public function getValue(): ?int
    {
        return 5;
    }
}

<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

abstract class SkipTernaryNoDirectUsage2
{
    public ?string $value;

    public function run(): ?string
    {
        return $this->value !== null ? $this->doSomething($this->value) : null;
    }

    abstract public function doSomething(string $value);
}
?>

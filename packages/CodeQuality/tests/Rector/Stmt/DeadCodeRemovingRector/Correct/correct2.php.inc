<?php

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector\Wrong;

class SomeClass
{
    private $value;

    /**
     * @var mixed[]
     */
    private $values = [];

    /**
     * @var callable[]
     */
    private $callable = [];

    public function first()
    {
        $this->value = 5;
    }

    public function second()
    {
        $this->values[] = 5;
    }

    public function third()
    {
        $this->callable[0]();
    }
}

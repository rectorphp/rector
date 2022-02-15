<?php

namespace Rector\Tests\Php74\Rector\Function_\ReservedFnFunctionRector\FixtureRenameNameNode;

class SkipVariableFuncCall
{
    /** @var list<Closure():void> */
    private array $callbacks = [];

    public function run()
    {
        foreach ($this->callbacks as $fn) {
            $fn();
        }
    }
}

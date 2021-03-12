<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

class SkipDoubleUseInOneMethodWithoutChange
{
    private $count;

    // first ->run() return 5
    // second ->run() return 10
    public function run()
    {
        if ($this->count === 5) {
            return 10;
        }

        $this->count = 5;

        return $this->count;
    }
}

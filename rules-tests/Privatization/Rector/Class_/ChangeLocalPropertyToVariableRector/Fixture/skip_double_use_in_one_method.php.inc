<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

class SkipDoubleUseInOneMethod
{
    private $count;

    // first ->run() return 5
    // second ->run() return 10
    public function run()
    {
        if ($this->count === 5) {
            $this->count = 10;
            return $this->count;
        }

        $this->count = 5;

        return $this->count;
    }
}

<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

class SkipDoubleUse
{
    private $count;
    public function run()
    {
        $this->count = 5;
        return $this->count;
    }

    public function go()
    {
        $this->count = 5;
    }
}

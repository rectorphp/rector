<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

class SkipPublic
{
    public $count;
    public function run()
    {
        $this->count = 5;
        return $this->count;
    }
}

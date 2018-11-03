<?php

namespace Rector\CodingStyle\Tests\Rector\If_\NullableCompareToNullRector\Wrong;

use stdClass;

class SomeClass
{
    public function run()
    {
        if ($this->getNullOrObject()) {
            return true;
        }

        return false;
    }

    private function getNullOrObject(): ?stdClass
    {
        return null;
    }
}

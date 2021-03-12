<?php

namespace Rector\Privatization\Tests\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector\Fixture;

use stdClass;

class SkipDifferentObjectGetter
{
    private $some;

    public function run()
    {
        $that = new stdClass();
        return $that->getSome() + 5;
    }

    private function getSome()
    {
        return $this->some + 1;
    }
}

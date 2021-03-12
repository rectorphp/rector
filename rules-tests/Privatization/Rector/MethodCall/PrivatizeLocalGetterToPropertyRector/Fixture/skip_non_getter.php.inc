<?php

namespace Rector\Tests\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector\Fixture;

class SkipNonGetter
{
    private $some;

    public function run()
    {
        return $this->getSome() + 5;
    }

    private function getSome()
    {
        return $this->some + 1;
    }
}

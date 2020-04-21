<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

class SkipClassExtended extends AbstractParentClass
{
    /**
     * @api
     */
    public function stop()
    {
        $this->run();
    }
}

abstract class AbstractParentClass
{
    protected function run()
    {
    }
}

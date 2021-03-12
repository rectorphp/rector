<?php

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

class SkipDynamicStaticCall
{
    public function getMethod(): object
    {
    }

    public function run()
    {
        $store = $this->getMethod();
        $store::foo();
    }
}

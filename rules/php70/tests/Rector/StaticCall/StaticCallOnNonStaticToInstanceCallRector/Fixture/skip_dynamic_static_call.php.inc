<?php

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

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

<?php

namespace Rector\Tests\Arguments\Rector\MethodCall\ValueObjectWrapArgRector\Fixture;

class SkipAlreadyNew
{
    public function run()
    {
        $this->something(new \Number(1000));
    }
}

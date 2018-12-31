<?php

namespace Rector\Php\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

class WithConstructor
{
    public function __construct($required, $value = [])
    {
    }

    public function doWork()
    {
        return 5;
    }
}

class TryWithContructor
{
    public function run()
    {
        return WithConstructor::doWork();
    }
}

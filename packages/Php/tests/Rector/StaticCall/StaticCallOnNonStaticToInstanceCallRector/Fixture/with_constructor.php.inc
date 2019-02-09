<?php

namespace Rector\Php\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

class WithConstructor
{
    private $required;

    public function __construct($required, $value = [])
    {
        $this->required = $required;
    }

    public function doWork()
    {
        $this->required = 5;
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

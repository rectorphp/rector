<?php

namespace Rector\Tests\Transform\Rector\New_\NewToStaticCallRector\Fixture;

class SkipAnotherClass
{
    public function run($name)
    {
        new AnotherClass($name);
    }
}

class AnotherClass
{
}

<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class Skip extends AnotherClass
{
    public function __construct()
    {
        parent::__construct();
    }
}

class AnotherClass
{
    public function __construct()
    {
        $value = 5;
    }
}

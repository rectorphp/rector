<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\Fixture;

class MyPdoStub extends MyPdo
{
    // empty on purpose for stub
    public function __construct($username, $password)
    {
    }
}

class MyPdo
{
    public function __construct($username, $password)
    {
        db_connect($username, $password);
    }
}

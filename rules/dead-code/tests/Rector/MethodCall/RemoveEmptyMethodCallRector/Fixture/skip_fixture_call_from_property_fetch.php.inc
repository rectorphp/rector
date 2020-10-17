<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

class ParentClass
{
    public $obj;

    public function __construct(EmptyMethod $obj)
    {
        $this->obj = $obj;
    }
}

class A extends ParentClass
{
    public function execute()
    {
        $this->obj->run();
    }
}

?>

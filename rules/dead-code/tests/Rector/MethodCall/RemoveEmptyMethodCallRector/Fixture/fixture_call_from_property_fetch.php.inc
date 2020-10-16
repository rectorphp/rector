<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

class A
{
    private $obj;

    public function __construct()
    {
        $this->obj = new EmptyMethod();
    }

    public function execute()
    {
        $this->obj->run();
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

class A
{
    private $obj;

    public function __construct()
    {
        $this->obj = new EmptyMethod();
    }

    public function execute()
    {
    }
}

?>

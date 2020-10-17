<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

class B
{
    private function getObj(): EmptyMethod
    {
        $obj = new EmptyMethod();
        return $obj;
    }

    public function execute()
    {
        $obj = $this->getObj();
        $obj->run();
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

class B
{
    private function getObj(): EmptyMethod
    {
        $obj = new EmptyMethod();
        return $obj;
    }

    public function execute()
    {
        $obj = $this->getObj();
    }
}

?>

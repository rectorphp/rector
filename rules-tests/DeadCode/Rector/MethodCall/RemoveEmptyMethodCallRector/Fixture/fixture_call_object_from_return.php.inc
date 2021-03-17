<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

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

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

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

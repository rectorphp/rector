<?php

namespace Rector\Order\Tests\Rector\Class_\OrderMethodsByVisibilityRector\Fixture;

class MagicPhpunitPreferredOrder
{

    protected function protectedFunctionName()
    {
    }
    public function tearDown()
    {
    }
    public function publicFunctionName()
    {
    }
    public function __construct()
    {
    }
    static private function staticPrivateFunctionName()
    {
    }

    public function __get($argument)
    {
    }
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderMethodsByVisibilityRector\Fixture;

class MagicPhpunitPreferredOrder
{

    public function __construct()
    {
    }
    public function __get($argument)
    {
    }
    public function tearDown()
    {
    }
    public function publicFunctionName()
    {
    }
    protected function protectedFunctionName()
    {
    }
    static private function staticPrivateFunctionName()
    {
    }
}

?>

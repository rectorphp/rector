<?php

namespace Rector\Order\Tests\Rector\Class_\OrderMethodsByVisibilityRector\Fixture;

abstract class AbstractClass
{
    abstract protected function abstractProtectedFunctionName();
    public function publicFunctionName()
    {

    }

    abstract public function abstractPublicFunctionName();

    abstract static public function abstractStaticPublicFunctionName();

    private function privateFunctionName()
    {

    }

    abstract static protected function abstractStaticProtectedFunctionName();

    protected function protectedFunctionName()
    {

    }
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderMethodsByVisibilityRector\Fixture;

abstract class AbstractClass
{
    abstract public function abstractPublicFunctionName();
    public function publicFunctionName()
    {

    }

    abstract static public function abstractStaticPublicFunctionName();
    abstract protected function abstractProtectedFunctionName();

    protected function protectedFunctionName()
    {

    }
    abstract static protected function abstractStaticProtectedFunctionName();
    private function privateFunctionName()
    {

    }
}

?>

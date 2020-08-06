<?php

namespace Rector\Order\Tests\Rector\ClassLike\OrderMethodsByVisibilityRector\Fixture;

abstract class AbstractClass
{
    abstract protected function abstractProtectedFunctionName();
    abstract public function abstractPublicFunctionName();

    abstract static public function abstractStaticPublicFunctionName();
    abstract static protected function abstractStaticProtectedFunctionName();
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\ClassLike\OrderMethodsByVisibilityRector\Fixture;

abstract class AbstractClass
{
    abstract public function abstractPublicFunctionName();
    abstract static public function abstractStaticPublicFunctionName();
    abstract protected function abstractProtectedFunctionName();
    abstract static protected function abstractStaticProtectedFunctionName();
}

?>

<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\FixturePhp74;

class VoidReturnTypeInArrowFunction2
{
    public function test() {}
}

if (fn() => (new VoidReturnTypeInArrowFunction2())->test()) {

}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\FixturePhp74;

class VoidReturnTypeInArrowFunction2
{
    public function test() {}
}

if (fn() => false) {

}

?>

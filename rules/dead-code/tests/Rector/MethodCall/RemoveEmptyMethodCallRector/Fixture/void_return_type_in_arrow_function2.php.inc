<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class VoidReturnTypeInArrayFunction2
{
    public function test() {}
}

if (fn() => (new VoidReturnTypeInArrayFunction())->test()) {

}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class VoidReturnTypeInArrayFunction2
{
    public function test() {}
}

if (fn() => false) {

}

?>

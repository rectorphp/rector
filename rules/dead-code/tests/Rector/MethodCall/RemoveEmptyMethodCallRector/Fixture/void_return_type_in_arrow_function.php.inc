<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class VoidReturnTypeInArrayFunction
{
    public function test() {}
}

fn() => (new VoidReturnTypeInArrayFunction())->test();

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class VoidReturnTypeInArrayFunction
{
    public function test() {}
}

?>

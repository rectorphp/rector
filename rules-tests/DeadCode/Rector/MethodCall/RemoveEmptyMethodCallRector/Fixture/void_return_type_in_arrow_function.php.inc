<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class VoidReturnTypeInArrowFunction
{
    public function test() {}
}

fn() => (new VoidReturnTypeInArrowFunction())->test();

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class VoidReturnTypeInArrowFunction
{
    public function test() {}
}

?>

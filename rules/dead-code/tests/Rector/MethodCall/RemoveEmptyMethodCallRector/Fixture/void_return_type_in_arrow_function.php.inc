<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class VoidReturnTypeInArrowFunction
{
    public function test() {}
}

fn() => (new VoidReturnTypeInArrowFunction())->test();

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class VoidReturnTypeInArrowFunction
{
    public function test() {}
}

?>

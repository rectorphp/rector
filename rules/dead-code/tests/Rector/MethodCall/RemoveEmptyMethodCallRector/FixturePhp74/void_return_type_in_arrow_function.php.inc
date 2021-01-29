<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\FixturePhp74;

class VoidReturnTypeInArrowFunction
{
    public function test() {}
}

fn() => (new VoidReturnTypeInArrowFunction())->test();

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\FixturePhp74;

class VoidReturnTypeInArrowFunction
{
    public function test() {}
}

?>

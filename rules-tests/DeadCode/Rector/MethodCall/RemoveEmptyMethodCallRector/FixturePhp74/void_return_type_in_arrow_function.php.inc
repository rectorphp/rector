<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\FixturePhp74;

class VoidReturnTypeInArrowFunction
{
    public function test() {}
}

fn() => (new VoidReturnTypeInArrowFunction())->test();

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\FixturePhp74;

class VoidReturnTypeInArrowFunction
{
    public function test() {}
}

?>

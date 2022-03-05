<?php

namespace Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Fixture;

use Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Source\MethodCaller;

final class SomeClass
{
    public function run(MethodCaller $caller)
    {
        $caller->process(1, 2);
    }
}

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Fixture;

use Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Source\MethodCaller;

final class SomeClass
{
    public function run(MethodCaller $caller)
    {
        $caller->process(1);
    }
}

?>

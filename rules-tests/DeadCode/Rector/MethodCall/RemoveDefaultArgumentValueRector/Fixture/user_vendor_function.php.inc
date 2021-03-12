<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

use function Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Source\userDefinedFunction;

class UserVendorFunction
{
    public function run()
    {
        userDefinedFunction([]);
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

use function Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Source\userDefinedFunction;

class UserVendorFunction
{
    public function run()
    {
        userDefinedFunction();
    }
}

?>

<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

use function Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector\Source\userDefinedFunction;

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

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

use function Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector\Source\userDefinedFunction;

class UserVendorFunction
{
    public function run()
    {
        userDefinedFunction();
    }
}

?>

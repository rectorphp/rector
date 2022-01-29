<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector\Fixture;

use stdClass;

class SomePrivatePropertyReadonly
{
    public function __construct(private readonly stdClass $someUnusedDependency)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector\Fixture;

use stdClass;

class SomePrivatePropertyReadonly
{
    public function __construct()
    {
    }
}

?>

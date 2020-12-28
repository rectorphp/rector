<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class DealWithPropertyFetches
{
    private $unused;
    private $prop;

    public function __construct($unused)
    {
        $this->unused = $unused;
    }

    public function foo()
    {
        $this->prop[] = 'foo';

    }
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class DealWithPropertyFetches
{
    public function __construct()
    {
    }
}
?>

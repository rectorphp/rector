<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class DealWithPropertyOfMethodCall
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

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class DealWithPropertyOfMethodCall
{
    public function __construct($unused)
    {
    }
}

?>

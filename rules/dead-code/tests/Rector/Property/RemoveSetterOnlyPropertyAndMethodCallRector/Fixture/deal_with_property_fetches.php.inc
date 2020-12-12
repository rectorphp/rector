<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class MyClass
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

class MyClass
{
    public function __construct()
    {
    }
}
?>

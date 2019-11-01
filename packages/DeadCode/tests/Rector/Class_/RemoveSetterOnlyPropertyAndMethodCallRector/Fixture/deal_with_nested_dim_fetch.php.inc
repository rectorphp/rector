<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class DealWithNestedDimFetch
{
    private $unused;

    private $prop;

    public function __construct($unused)
    {
        $this->unused[][][] = $unused;
    }

    public function foo()
    {
        $this->prop[][][][][][] = 'foo';
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class DealWithNestedDimFetch
{
    public function __construct($unused)
    {
    }
    public function foo()
    {
    }
}

?>

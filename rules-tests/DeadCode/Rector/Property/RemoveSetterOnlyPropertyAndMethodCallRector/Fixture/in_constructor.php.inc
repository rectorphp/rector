<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class InConstructor
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class InConstructor
{
    public function __construct()
    {
    }
}

?>

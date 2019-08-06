<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class InConstructor
{
    public function __construct($name)
    {
    }
}

?>

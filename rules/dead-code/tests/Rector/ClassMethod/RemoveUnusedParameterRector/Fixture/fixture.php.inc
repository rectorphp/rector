<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class Fixture
{
    public function __construct($value, $value2)
    {
        $this->value = $value;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class Fixture
{
    public function __construct($value)
    {
        $this->value = $value;
    }
}

?>

<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class InBetweenParameter
{
    private $value;
    private $value3;

    public function __construct($value, $value2, $value3)
    {
        $this->value = $value;
        $this->value3 = $value3;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class InBetweenParameter
{
    private $value;
    private $value3;

    public function __construct($value, $value3)
    {
        $this->value = $value;
        $this->value3 = $value3;
    }
}

?>

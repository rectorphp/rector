<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class Order
{
    public function aloha($value, $value2, $value3, $value4)
    {
        $result = $value + $value3;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

class Order
{
    public function aloha($value, $value2, $value3)
    {
        $result = $value + $value3;
    }
}

?>

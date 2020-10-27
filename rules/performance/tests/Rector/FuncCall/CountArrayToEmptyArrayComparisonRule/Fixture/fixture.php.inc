<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRule\Fixture;

class SomeClass
{
    public function run(array $array)
    {
        count($array) === 0;
        count($array) > 0;
    }
}

?>
-----
<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRule\Fixture;

class SomeClass
{
    public function run(array $array)
    {
        $array === [];
        $array !== [];
    }
}

?>

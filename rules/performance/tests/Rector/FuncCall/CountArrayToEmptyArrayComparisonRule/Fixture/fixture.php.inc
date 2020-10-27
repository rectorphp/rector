<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRule\Fixture;

class SomeClass
{
    public function run(array $array)
    {
        // from parameter
        count($array) === 0;
        count($array) > 0;

        // from local variable
        $array = [];
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
        // from parameter
        $array === [];
        $array !== [];

        // from local variable
        $array = [];
        $array === [];
        $array !== [];
    }
}

?>

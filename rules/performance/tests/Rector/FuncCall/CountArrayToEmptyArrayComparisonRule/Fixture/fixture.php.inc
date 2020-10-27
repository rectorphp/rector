<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRule\Fixture;

class SomeClass
{
    public function run(array $array)
    {
        // from parameter
        count($array) === 0;
        0 === count($array);
        count($array) > 0;

        // from local variable
        $localArray = [];
        count($localArray) === 0;
        count($localArray) > 0;
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
        [] === $array;
        $array !== [];

        // from local variable
        $localArray = [];
        $localArray === [];
        $localArray !== [];
    }
}

?>

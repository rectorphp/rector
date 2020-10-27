<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClass
{
    public function run(array $array)
    {
        // from parameter
        count($array) === 0;
        0 === count($array);
        count($array) > 0;
        0 < count($array);

        // from local variable
        $localArray = [];
        count($localArray) === 0;
        0 === count($localArray);
        count($localArray) > 0;
        0 < count($localArray);
    }
}

?>
-----
<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClass
{
    public function run(array $array)
    {
        // from parameter
        $array === [];
        [] === $array;
        $array !== [];
        [] !== $array;

        // from local variable
        $localArray = [];
        $localArray === [];
        [] === $localArray;
        $localArray !== [];
        [] !== $localArray;
    }
}

?>

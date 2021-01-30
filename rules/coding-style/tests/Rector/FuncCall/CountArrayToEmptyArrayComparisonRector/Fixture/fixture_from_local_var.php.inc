<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureFromLocalVar
{
    public function run()
    {
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

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureFromLocalVar
{
    public function run()
    {
        $localArray = [];
        $localArray === [];
        [] === $localArray;
        $localArray !== [];
        [] !== $localArray;
    }
}

?>

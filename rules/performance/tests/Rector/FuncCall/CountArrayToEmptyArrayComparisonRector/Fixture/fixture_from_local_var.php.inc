<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClassFromLocalVar
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

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClassFromLocalVar
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

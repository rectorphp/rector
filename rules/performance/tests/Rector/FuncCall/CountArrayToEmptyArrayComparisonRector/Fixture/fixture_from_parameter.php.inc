<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureFromParameter
{
    public function run(array $array)
    {
        count($array) === 0;
        0 === count($array);
        count($array) > 0;
        0 < count($array);
    }
}

?>
-----
<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureFromParameter
{
    public function run(array $array)
    {
        $array === [];
        [] === $array;
        $array !== [];
        [] !== $array;
    }
}

?>

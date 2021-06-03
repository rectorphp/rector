<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixturNotIdentical
{
    public function run()
    {
        function ($value, array $keys) {
            return 0 !== count($keys) && is_array($value);
        };
    }

    public function run2()
    {
        function ($value, array $keys) {
            return count($keys) !== 0 && is_array($value);
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixturNotIdentical
{
    public function run()
    {
        function ($value, array $keys) {
            return [] !== $keys && is_array($value);
        };
    }

    public function run2()
    {
        function ($value, array $keys) {
            return $keys !== [] && is_array($value);
        };
    }
}

?>

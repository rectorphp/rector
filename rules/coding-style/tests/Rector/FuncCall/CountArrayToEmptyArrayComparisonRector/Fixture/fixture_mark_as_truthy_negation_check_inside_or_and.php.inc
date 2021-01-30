<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyNegationCheckInsideOrAnd
{
    public function run()
    {
        $data = [];
        true || ! count($data);
        ! count($data) || true;
        true && ! count($data);
        ! count($data) && true;
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyNegationCheckInsideOrAnd
{
    public function run()
    {
        $data = [];
        true || $data === [];
        $data === [] || true;
        true && $data === [];
        $data === [] && true;
    }
}

?>

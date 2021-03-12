<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyNegationCheck
{
    public function run()
    {
        $data = [];
        ! count($data);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyNegationCheck
{
    public function run()
    {
        $data = [];
        $data === [];
    }
}

?>

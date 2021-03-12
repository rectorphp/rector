<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyNegationCheck
{
    public function run()
    {
        $data = [];
        $data === [];
    }
}

?>

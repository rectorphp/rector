<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

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

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyNegationCheck
{
    public function run()
    {
        $data = [];
        $data === [];
    }
}

?>

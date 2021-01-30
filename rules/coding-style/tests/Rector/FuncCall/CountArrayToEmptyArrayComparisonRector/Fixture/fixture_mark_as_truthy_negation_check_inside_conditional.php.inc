<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

final class FixtureMarkAsTruthyNegationCheckInsideConditional
{
    public function run()
    {
        $data = [];
        if (! count($data)) {

        }
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

final class FixtureMarkAsTruthyNegationCheckInsideConditional
{
    public function run()
    {
        $data = [];
        if ($data === []) {

        }
    }
}

?>

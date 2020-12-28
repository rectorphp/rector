<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyNegationCheckInsideConditional
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

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyNegationCheckInsideConditional
{
    public function run()
    {
        $data = [];
        if ($data === []) {

        }
    }
}

?>

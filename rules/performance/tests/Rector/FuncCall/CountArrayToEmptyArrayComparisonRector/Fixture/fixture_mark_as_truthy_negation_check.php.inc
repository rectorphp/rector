<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClassMarkTruthyNegationCheck
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

class SomeClassMarkTruthyNegationCheck
{
    public function run()
    {
        $data = [];
        $data === [];
    }
}

?>

<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClassMarkTruthyInsideOrAnd
{
    public function run()
    {
        $data = [];
        true || ! count($data);
        true && ! count($data);
    }
}

?>
-----
<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClassMarkTruthyInsideOrAnd
{
    public function run()
    {
        $data = [];
        true || $data === [];
        true && $data === [];
    }
}

?>

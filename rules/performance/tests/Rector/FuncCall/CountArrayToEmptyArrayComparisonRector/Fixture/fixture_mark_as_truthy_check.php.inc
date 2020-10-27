<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClassMarkTruthyCheck
{
    public function run()
    {
        $data = [];
        $otherData = [];

        if (count($data)) {

        } elseif (count($otherData)) {

        }

        while (count($data)) {
            $data = [];
        }
    }
}

?>
-----
<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class SomeClassMarkTruthyCheck
{
    public function run()
    {
        $data = [];
        $otherData = [];

        if ($data !== []) {

        } elseif ($otherData !== []) {

        }

        while ($data !== []) {
            $data = [];
        }
    }
}

?>

<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class ClosureClass
{
    public function run()
    {
        $datetime = new DateTime();
        array_map(function ($element) use ($datetime) {
            $a = $datetime->getTimestamp() + $element;
            return $a;
        }, [1, 2, 3]);
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class ClosureClass
{
    public function run()
    {
        $datetime = new DateTime();
        array_map(function ($element) use ($datetime) {
            $timestamp = $datetime->getTimestamp() + $element;
            return $timestamp;
        }, [1, 2, 3]);
    }
}

?>

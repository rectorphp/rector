<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use DateTime;

class ClosureClass
{
    public function run()
    {
        $datetime = new DateTime();
        array_map(function ($element) use ($datetime) {
            $a = $datetime->getTimestamp();
            return $a;
        }, [1, 2, 3]);
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use DateTime;

class ClosureClass
{
    public function run()
    {
        $datetime = new DateTime();
        array_map(function ($element) use ($datetime) {
            $timestamp = $datetime->getTimestamp();
            return $timestamp;
        }, [1, 2, 3]);
    }
}

?>

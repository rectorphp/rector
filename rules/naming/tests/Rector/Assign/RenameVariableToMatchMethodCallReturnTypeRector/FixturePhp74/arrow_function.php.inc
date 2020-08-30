<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\FixturePhp74;

use DateTime;

class ArrowFunction
{
    public function run()
    {
        $datetime = new DateTime();

        $fn1 = fn($x) => $x + $stamp = $datetime->getTimezone();
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\FixturePhp74;

use DateTime;

class ArrowFunction
{
    public function run()
    {
        $datetime = new DateTime();

        $fn1 = fn($x) => $x + $dateTimeZone = $datetime->getTimezone();
    }
}

?>

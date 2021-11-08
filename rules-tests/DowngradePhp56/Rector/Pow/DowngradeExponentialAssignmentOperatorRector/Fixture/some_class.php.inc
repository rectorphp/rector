<?php

namespace Rector\Tests\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $x = 2;
        $x **= 3;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $x = 2;
        $x = pow($x, 3);
    }
}

?>

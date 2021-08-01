<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class ExistingVariableAssign
{
    public function run($a, $b)
    {
        $battleShipcompare = 'test';
        if ($a <=> $b) {
        }
    }

    public function run2($a, $b)
    {
        $battleShipcompare = 'test';
        $battleShipcompare2 = 'test2';
        if ($a <=> $b) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class ExistingVariableAssign
{
    public function run($a, $b)
    {
        $battleShipcompare = 'test';
        $battleShipcompare2 = function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        };
        if ($battleShipcompare2($a, $b)) {
        }
    }

    public function run2($a, $b)
    {
        $battleShipcompare = 'test';
        $battleShipcompare2 = 'test2';
        $battleShipcompare3 = function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        };
        if ($battleShipcompare3($a, $b)) {
        }
    }
}

?>

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
        $battleShipcompare1 = function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        };
        if ($battleShipcompare1($a, $b)) {
        }
    }
}

?>

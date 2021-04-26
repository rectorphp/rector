<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class InIf
{
    public function run($a, $b)
    {
        if ($a <=> $b) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class InIf
{
    public function run($a, $b)
    {
        $battleShipcompare = function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        };
        if ($battleShipcompare($a, $b)) {
        }
    }
}

?>

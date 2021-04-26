<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class Fixture
{
    public function run($a, $b)
    {
        return $a <=> $b;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class Fixture
{
    public function run($a, $b)
    {
        $battleShipcompare = function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        };
        return $battleShipcompare($a, $b);
    }
}

?>

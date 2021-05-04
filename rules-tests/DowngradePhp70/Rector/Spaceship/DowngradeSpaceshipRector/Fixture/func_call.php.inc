<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

function getRand()
{
    return rand(1,100);
}

class SomeFuncCall
{
    public function run()
    {
        return getRand() <=> getRand();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

function getRand()
{
    return rand(1,100);
}

class SomeFuncCall
{
    public function run()
    {
        $battleShipcompare = function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        };
        return $battleShipcompare(getRand(), getRand());
    }
}

?>

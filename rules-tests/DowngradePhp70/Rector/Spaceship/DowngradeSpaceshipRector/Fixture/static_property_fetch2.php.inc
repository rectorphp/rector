<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class StaticPropertyFetch2
{
    public static $a;
    public static $b;

    public function run()
    {
        return StaticPropertyFetch2::$a <=> StaticPropertyFetch2::$b;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class StaticPropertyFetch2
{
    public static $a;
    public static $b;

    public function run()
    {
        $battleShipcompare = function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        };
        return $battleShipcompare(StaticPropertyFetch2::$a, StaticPropertyFetch2::$b);
    }
}

?>

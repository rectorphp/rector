<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

final class SomeStaticPropertyFetch
{
    private static $a;
    private static $b;

    public function run()
    {
        return self::$a <=> self::$b;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

final class SomeStaticPropertyFetch
{
    private static $a;
    private static $b;

    public function run()
    {
        $battleShipcompare = function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        };
        return $battleShipcompare(self::$a, self::$b);
    }
}

?>

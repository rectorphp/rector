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
        return (function ($a, $b) {
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        })(StaticPropertyFetch2::$a, StaticPropertyFetch2::$b);
    }
}

?>

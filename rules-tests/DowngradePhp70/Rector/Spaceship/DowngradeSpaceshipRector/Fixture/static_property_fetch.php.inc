<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class StaticPropertyFetch
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

class StaticPropertyFetch
{
    private static $a;
    private static $b;

    public function run()
    {
        return (function ($a, $b) {
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        })(self::$a, self::$b);
    }
}

?>

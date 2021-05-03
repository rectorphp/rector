<?php

namespace Rector\Tests\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

class StaticPropertyFetch
{
    private static $var;

    public function run()
    {
        self::$var = 'abc';
        echo self::$var[-2];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector\Fixture;

class StaticPropertyFetch
{
    private static $var;

    public function run()
    {
        self::$var = 'abc';
        echo self::$var[strlen(self::$var) - 2];
    }
}

?>

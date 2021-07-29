<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector\Fixture;

class SomeStaticPropertyFetch
{
    public function run()
    {
        $username = self::$property ?? 'value';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector\Fixture;

class SomeStaticPropertyFetch
{
    public function run()
    {
        $username = isset(self::$property) ? self::$property : 'value';
    }
}

?>

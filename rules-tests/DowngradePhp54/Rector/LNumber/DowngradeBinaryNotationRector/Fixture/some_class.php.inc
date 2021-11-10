<?php

namespace Rector\Tests\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return 0b11111100101 + 0B01;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return 2021 + 1;
    }
}

?>

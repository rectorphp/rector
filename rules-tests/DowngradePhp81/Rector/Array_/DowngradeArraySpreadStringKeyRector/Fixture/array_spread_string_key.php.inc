<?php

namespace Rector\Tests\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector\Fixture;

class ArraySpreadStringKey
{
    public function run()
    {
        $parts = ['a' => 'b'];
        $parts2 = ['c' => 'd'];

        $result = [...$parts, ...$parts2];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector\Fixture;

class ArraySpreadStringKey
{
    public function run()
    {
        $parts = ['a' => 'b'];
        $parts2 = ['c' => 'd'];

        $result = [...$parts, ...$parts2];
    }
}

?>

<?php

namespace Rector\Tests\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector\Fixture;

class ArraySpreadStringKeyByDoc
{
    public function run()
    {
        /** @var array<string, string> $parts */
        $parts = $this->data();
        /** @var array<string, string> $parts2 */
        $parts2 = $this->data2();

        $result = [...$parts, ...$parts2];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector\Fixture;

class ArraySpreadStringKeyByDoc
{
    public function run()
    {
        /** @var array<string, string> $parts */
        $parts = $this->data();
        /** @var array<string, string> $parts2 */
        $parts2 = $this->data2();

        $result = [...$parts, ...$parts2];
    }
}

?>

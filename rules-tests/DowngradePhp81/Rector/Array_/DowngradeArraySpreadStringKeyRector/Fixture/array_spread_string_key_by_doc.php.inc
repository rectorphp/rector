<?php

namespace Rector\Tests\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector\Fixture;

final class ArraySpreadStringKeyByDoc
{
    public function run()
    {
        /** @var array<string, string> $parts */
        $parts = $this->data();
        /** @var array<string, string> $parts2 */
        $parts2 = $this->data();

        $result = [...$parts, ...$parts2];
    }

    /**
     * @return array<string, string>
     */
    private function data(): array
    {
        return ['your' => 'name'];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector\Fixture;

final class ArraySpreadStringKeyByDoc
{
    public function run()
    {
        /** @var array<string, string> $parts */
        $parts = $this->data();
        /** @var array<string, string> $parts2 */
        $parts2 = $this->data();

        $result = array_merge($parts, $parts2);
    }

    /**
     * @return array<string, string>
     */
    private function data(): array
    {
        return ['your' => 'name'];
    }
}

?>

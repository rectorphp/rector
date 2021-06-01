<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class FlipOrder
{
    private function execute(?array $a = null, ?array $b = null)
    {
        var_dump($b);
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute(b: [[$name ?? 0 => $attributes]], a: []);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class FlipOrder
{
    private function execute(?array $a = null, ?array $b = null)
    {
        var_dump($b);
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute([], [[$name ?? 0 => $attributes]]);
    }
}

?>

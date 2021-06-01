<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class Fixture2
{
    private function execute(?array $a = null, ?array $b = null)
    {
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute(b: [[$name ?? 0 => $attributes]]);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class Fixture2
{
    private function execute(?array $a = null, ?array $b = null)
    {
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute(null, [[$name ?? 0 => $attributes]]);
    }
}

?>

<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class Fixture
{
    private function execute(?array $a = null, ?array $b = null)
    {
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute(a: [[$name ?? 0 => $attributes]]);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

class Fixture
{
    private function execute(?array $a = null, ?array $b = null)
    {
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute([[$name ?? 0 => $attributes]]);
    }
}

?>

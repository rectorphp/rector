<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\SwitchNegatedTernaryRector\Fixture;

class Fixture
{
    public function run(bool $upper, string $name)
    {
        return ! $upper
            ? $name
            : strtoupper($name);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\SwitchNegatedTernaryRector\Fixture;

class Fixture
{
    public function run(bool $upper, string $name)
    {
        return $upper
            ? strtoupper($name)
            : $name;
    }
}

?>

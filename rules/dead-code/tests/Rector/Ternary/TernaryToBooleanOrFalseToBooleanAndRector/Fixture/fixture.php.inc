<?php

namespace Rector\DeadCode\Tests\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector\Fixture;

class Fixture
{
    public function go()
    {
        return $value ? $this->getBool() : false;
    }

    private function getBool(): bool
    {
        return (bool) 5;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector\Fixture;

class Fixture
{
    public function go()
    {
        return $value && $this->getBool();
    }

    private function getBool(): bool
    {
        return (bool) 5;
    }
}

?>

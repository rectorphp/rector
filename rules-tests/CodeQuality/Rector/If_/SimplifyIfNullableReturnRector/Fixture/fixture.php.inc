<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class Fixture
{
    public function run()
    {
        /** @var \stdClass|null $value */
        $value = $this->foo->bar();
        if (! $value instanceof \stdClass) {
            return null;
        }

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class Fixture
{
    public function run()
    {
        return $this->foo->bar();
    }
}

?>

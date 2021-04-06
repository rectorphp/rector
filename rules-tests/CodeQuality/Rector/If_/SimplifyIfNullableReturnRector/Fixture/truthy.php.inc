<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class Truthy
{
    public function run()
    {
        /** @var \stdClass|null $value */
        $value = $this->foo->bar();
        if ($value instanceof \stdClass) {
            return $value;
        }

        return null;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class Truthy
{
    public function run()
    {
        return $this->foo->bar();
    }
}

?>

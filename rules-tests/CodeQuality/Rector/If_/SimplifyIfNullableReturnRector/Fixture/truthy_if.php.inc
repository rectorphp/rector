<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

class TruthyIf
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

class TruthyIf
{
    public function run()
    {
        return $this->foo->bar();
    }
}

?>

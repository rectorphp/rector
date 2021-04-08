<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class SkipDifferentVariableIfReturn
{
    public function run()
    {
        /** @var \stdClass|null $value */
        $value = $this->foo->bar();

        if (! $value instanceof \stdClass) {
            return $abc;
        }

        return $value;
    }
}

?>

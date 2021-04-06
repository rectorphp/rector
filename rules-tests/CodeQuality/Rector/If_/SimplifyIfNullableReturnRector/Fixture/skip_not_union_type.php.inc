<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class SkipNotUnionType
{
    public function run()
    {
        /** @var \stdClass $value */
        $value = $this->foo->bar();
        if (! $value instanceof \stdClass) {
            return $value;
        }

        return null;
    }
}

?>

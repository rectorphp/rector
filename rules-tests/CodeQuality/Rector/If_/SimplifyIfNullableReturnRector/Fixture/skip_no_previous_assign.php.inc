<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class SkipNoPreviousAssign
{
    public function run()
    {
        if (! $value instanceof \stdClass) {
            return null;
        }

        return $value;
    }
}

?>

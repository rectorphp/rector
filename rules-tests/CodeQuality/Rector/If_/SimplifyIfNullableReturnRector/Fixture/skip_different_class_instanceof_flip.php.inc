<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class SkipDifferentClassInstanceOfFlip
{
    public function run()
    {
        /** @var null|\stdClass $value */
        $value = $this->foo->bar();

        if (! $value instanceof \DateTime) {
            return null;
        }

        return $value;
    }
}

?>

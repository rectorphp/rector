<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class SkipDifferentClassInstanceOf
{
    public function run()
    {
        /** @var \stdClass|null $value */
        $value = $this->foo->bar();

        if (! $value instanceof \DateTime) {
            return null;
        }

        return $value;
    }
}

?>

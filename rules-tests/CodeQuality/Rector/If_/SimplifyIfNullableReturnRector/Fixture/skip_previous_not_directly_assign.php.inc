<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class SkipPreviousNotDirectlyAssign
{
    public function run()
    {
        if (rand(0, 1)) {
            /** @var \stdClass|null $value */
            $value = $this->foo->bar();
        }

        if (! $value instanceof \stdClass) {
            return null;
        }

        return $value;
    }
}

?>

<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class SkipNextExpression
{
    public function run()
    {
        /** @var \stdClass|null $value */
        $value = $this->foo->bar();
        if (! $value instanceof \stdClass) {
            return null;
        }

        $value = rand(0,1);
    }
}

?>

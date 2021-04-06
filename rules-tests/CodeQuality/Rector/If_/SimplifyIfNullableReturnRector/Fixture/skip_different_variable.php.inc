<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class SkipDifferentVariable
{
    public function run()
    {
        /** @var \stdClass|null $value */
        $value = $this->foo->bar();

        if (! $value instanceof \stdClass) {
            return null;
        }

        return $anotherVariable;
    }
}

?>

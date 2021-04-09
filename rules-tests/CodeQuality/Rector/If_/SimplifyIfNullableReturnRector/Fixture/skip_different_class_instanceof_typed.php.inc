<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\Fixture;

final class SkipDifferentClassInstanceOfTyped
{
    private function bar(): ?\stdClass
    {
        if (rand(0, 1)) {
            return new \stdClass;
        }

        return null;
    }

    public function run()
    {
        $value = $this->bar();

        if (! $value instanceof \DateTime) {
            return null;
        }

        return $value;
    }
}

?>

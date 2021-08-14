<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureTernaryIsNull {
    public function ternary() {
        $b = $a === null ?: $a->getB();
    }
}
?>
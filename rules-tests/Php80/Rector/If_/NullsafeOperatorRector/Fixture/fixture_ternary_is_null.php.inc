<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureTernaryIsNull {
    public function ternary() {
        $b = $a === null ? null : $a->getB();
        $c = $a === null ? null : $a->c;
    }
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureTernaryIsNull {
    public function ternary() {
        $b = $a?->getB();
        $c = $a?->c;
    }
}
?>
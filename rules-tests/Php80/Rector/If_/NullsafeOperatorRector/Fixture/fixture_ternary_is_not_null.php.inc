<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureTernaryIsNotNull {
    public function ternary() {
        $b = $a !== null ? $a->getB() : null;
        $c = $a !== null ? $a->c : null;
    }
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class FixtureTernaryIsNotNull {
    public function ternary() {
        $b = $a?->getB();
        $c = $a?->c;
    }
}
?>
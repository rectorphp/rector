<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector\Fixture;

class StaticShouldNotBeNamespaced {
    public function test() {
        if (\get_class($other) !== static::class) {}
    }
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector\Fixture;

class StaticShouldNotBeNamespaced {
    public function test() {
        if (!$other instanceof static) {}
    }
}
?>

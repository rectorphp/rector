<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector\Fixture;

class StaticShouldNotBeNamespaced {
    public function test() {
        if (\get_class($other) !== static::class) {}
        if (static::class !== \get_class($other)) {}
    }
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector\Fixture;

class StaticShouldNotBeNamespaced {
    public function test() {
        if (!$other instanceof static) {}
        if (!$other instanceof static) {}
    }
}
?>

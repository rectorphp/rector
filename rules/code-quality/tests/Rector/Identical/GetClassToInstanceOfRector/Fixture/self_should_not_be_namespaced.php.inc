<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector\Fixture;

class SelfShouldNotBeNamespaced {
    public function test() {
        if (\get_class($other) !== self::class) {}
        if (self::class !== \get_class($other)) {}
    }
}
?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector\Fixture;

class SelfShouldNotBeNamespaced {
    public function test() {
        if (!$other instanceof self) {}
        if (!$other instanceof self) {}
    }
}
?>

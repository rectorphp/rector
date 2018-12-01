<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Rename;

class A {
    /**
     * @param string $a
     * @param int $b
     */
    public function test($a, $b) {}
}

class B extends A {
    // Typehints are position, not name based
    public function test($b, $a) {}
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Rename;

class A {
    /**
     * @param string $a
     * @param int $b
     */
    public function test(string $a, int $b) {}
}

class B extends A {
    // Typehints are position, not name based
    public function test(string $b, int $a) {}
}

?>

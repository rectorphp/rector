<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Basic;

/**
 * @param int $a
 * @param int $b
 * @return int
 */
function add2($a, $b) {
    return $a + $b;
}

class Number {
    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public function add2($a, $b) {
        return $a + $b;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Basic;

/**
 * @param int $a
 * @param int $b
 * @return int
 */
function add2($a, $b): int {
    return $a + $b;
}

class Number {
    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public function add2($a, $b): int {
        return $a + $b;
    }
}

?>

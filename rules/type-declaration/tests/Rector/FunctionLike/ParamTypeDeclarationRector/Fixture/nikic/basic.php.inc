<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\Basic;

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

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\Basic;

/**
 * @return int
 */
function add2(int $a, int $b) {
    return $a + $b;
}

class Number {
    /**
     * @return int
     */
    public function add2(int $a, int $b) {
        return $a + $b;
    }
}

?>

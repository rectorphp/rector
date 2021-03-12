<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class UnionConst
{
    const A = "A";
    const B = "B";

    /**
     * @var self::A|self::B
     */
    public $y;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class UnionConst
{
    /**
     * @var string
     */
    const A = "A";
    /**
     * @var string
     */
    const B = "B";

    /**
     * @var self::A|self::B
     */
    public $y;
}

?>

<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class Arrays
{
    public const NAME = ['Papa', 'Mama'];

    public const MIX = ['Papa', 5];

    public const PRICE = [5.0];

    public const IS_IT_TRUE = [false, true];

    public const IS_IT_DEEP_TRUE = [false, true, [true]];
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class Arrays
{
    /**
     * @var string[]
     */
    public const NAME = ['Papa', 'Mama'];

    /**
     * @var int[]|string[]
     */
    public const MIX = ['Papa', 5];

    /**
     * @var float[]
     */
    public const PRICE = [5.0];

    /**
     * @var bool[]
     */
    public const IS_IT_TRUE = [false, true];

    /**
     * @var bool[][]|bool[]
     */
    public const IS_IT_DEEP_TRUE = [false, true, [true]];
}

?>

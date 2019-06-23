<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class MiscType
{
    private const NUMBER = 5;
    private const NUMBER_NEGATIVE = -5;
    const STRING = 'name';
    const ITEMs = [[self::STRING]];

    private const A = false, B = 2.5;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class MiscType
{
    /**
     * @var int
     */
    private const NUMBER = 5;
    /**
     * @var int
     */
    private const NUMBER_NEGATIVE = -5;
    /**
     * @var string
     */
    const STRING = 'name';
    /**
     * @var string[][]
     */
    const ITEMs = [[self::STRING]];

    private const A = false, B = 2.5;
}

?>

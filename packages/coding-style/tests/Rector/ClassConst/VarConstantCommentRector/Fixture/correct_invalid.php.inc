<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class CorrectInvalid
{
    /**
     * @var int
     */
    public const NAME = 'Toma';

    /**
     * @var int
     */
    public const COUNT = 5;
    public const USER = 'me';

    /**
     * Some comment, but no type.
     */
    public const HI = 5;

    /**
     * Some comment and right type, with a line between.
     *
     * @var string[]
     */
    public const HELLO = [
        'string'
    ];

    /**
     * Some comment and wrong type, with a line between.
     *
     * @var int
     */
    public const HELLO_AGAIN = [
        'string'
    ];
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class CorrectInvalid
{
    /**
     * @var string
     */
    public const NAME = 'Toma';

    /**
     * @var int
     */
    public const COUNT = 5;
    /**
     * @var string
     */
    public const USER = 'me';

    /**
     * Some comment, but no type.
     * @var int
     */
    public const HI = 5;

    /**
     * Some comment and right type, with a line between.
     *
     * @var string[]
     */
    public const HELLO = [
        'string'
    ];

    /**
     * Some comment and wrong type, with a line between.
     *
     * @var string[]
     */
    public const HELLO_AGAIN = [
        'string'
    ];
}

?>

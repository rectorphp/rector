<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class ExplicitKeyArray
{
    const VALUES_WITH_KEYS = [
        100 => 'hi'
    ];

    const VALUES_WITHOUT_KEYS = [
        'hi'
    ];
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class ExplicitKeyArray
{
    /**
     * @var string[]
     */
    const VALUES_WITH_KEYS = [
        100 => 'hi'
    ];

    /**
     * @var string[]
     */
    const VALUES_WITHOUT_KEYS = [
        'hi'
    ];
}

?>

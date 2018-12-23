<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\CompleteVarDocTypeConstantRector\Fixture;

final class SomeClass
{
    private const NUMBER = 5;
    const STRING = 'name';
    const ITEMs = [[self::STRING]];

    private const A = false, B = 2.5;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\CompleteVarDocTypeConstantRector\Fixture;

final class SomeClass
{
    /**
     * @var int
     */
    private const NUMBER = 5;
    /**
     * @var string
     */
    const STRING = 'name';
    /**
     * @var mixed[]
     */
    const ITEMs = [[self::STRING]];

    private const A = false, B = 2.5;
}

?>
